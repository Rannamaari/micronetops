<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillParticipant;
use App\Models\BillSharedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RattehinController extends Controller
{
    public function index()
    {
        $bills = Bill::where('user_id', auth()->id())
            ->with(['participants', 'items', 'sharedItems'])
            ->latest()
            ->paginate(10);

        return view('rattehin.index', compact('bills'));
    }

    public function create()
    {
        return view('rattehin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'restaurant_name' => 'nullable|string|max:255',
            'service_charge_percentage' => 'required|numeric|min:0|max:100',
            'gst_enabled' => 'boolean',
            'gst_on_service' => 'boolean',
            'currency' => 'string|max:3',
            'participants' => 'required|array|min:1',
            'participants.*.name' => 'required|string|max:255',
            'items' => 'array',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.assigned_to' => 'array',
            'shared_items' => 'array',
            'shared_items.*.name' => 'required|string|max:255',
            'shared_items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Create the bill
            $bill = Bill::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'] ?? 'Untitled Bill',
                'restaurant_name' => $validated['restaurant_name'] ?? null,
                'service_charge_percentage' => $validated['service_charge_percentage'] ?? 0,
                'gst_enabled' => $validated['gst_enabled'] ?? false,
                'gst_on_service' => $validated['gst_on_service'] ?? false,
                'currency' => $validated['currency'] ?? 'MVR',
            ]);

            // Create participants
            $participants = [];
            foreach ($validated['participants'] as $participantData) {
                $participants[] = $bill->participants()->create([
                    'name' => $participantData['name'],
                ]);
            }

            // Create items
            if (isset($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $bill->items()->create([
                        'name' => $itemData['name'],
                        'price' => $itemData['price'],
                        'assigned_to' => $itemData['assigned_to'] ?? [],
                    ]);
                }
            }

            // Create shared items
            if (isset($validated['shared_items'])) {
                foreach ($validated['shared_items'] as $sharedItemData) {
                    $bill->sharedItems()->create([
                        'name' => $sharedItemData['name'],
                        'price' => $sharedItemData['price'],
                    ]);
                }
            }

            // Calculate totals
            $this->calculateBillTotals($bill);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('rattehin.show', $bill)
                ]);
            }

            return redirect()->route('rattehin.show', $bill)
                ->with('success', 'Bill created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create bill: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Failed to create bill: ' . $e->getMessage());
        }
    }

    public function show(Bill $bill)
    {
        if ($bill->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this bill.');
        }

        $bill->load(['participants', 'items', 'sharedItems']);

        return view('rattehin.show', compact('bill'));
    }

    public function edit(Bill $bill)
    {
        if ($bill->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this bill.');
        }

        $bill->load(['participants', 'items', 'sharedItems']);

        // Format bill data for JavaScript
        $billData = [
            'title' => $bill->title,
            'restaurant_name' => $bill->restaurant_name,
            'service_charge_percentage' => (float) $bill->service_charge_percentage,
            'gst_enabled' => (bool) $bill->gst_enabled,
            'gst_on_service' => (bool) $bill->gst_on_service,
            'currency' => $bill->currency,
            'participants' => $bill->participants->map(fn($p) => ['name' => $p->name])->values(),
            'items' => $bill->items->map(fn($item) => [
                'name' => $item->name,
                'price' => (float) $item->price,
                'assigned_to' => $item->assigned_to ?? []
            ])->values(),
            'shared_items' => $bill->sharedItems->map(fn($item) => [
                'name' => $item->name,
                'price' => (float) $item->price
            ])->values(),
        ];

        return view('rattehin.edit', [
            'bill' => $bill,
            'billData' => $billData
        ]);
    }

    public function update(Request $request, Bill $bill)
    {
        if ($bill->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this bill.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'restaurant_name' => 'nullable|string|max:255',
            'service_charge_percentage' => 'required|numeric|min:0|max:100',
            'gst_enabled' => 'boolean',
            'gst_on_service' => 'boolean',
            'currency' => 'string|max:3',
            'participants' => 'required|array|min:1',
            'participants.*.name' => 'required|string|max:255',
            'items' => 'array',
            'items.*.name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.assigned_to' => 'array',
            'shared_items' => 'array',
            'shared_items.*.name' => 'required|string|max:255',
            'shared_items.*.price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Update bill
            $bill->update([
                'title' => $validated['title'] ?? 'Untitled Bill',
                'restaurant_name' => $validated['restaurant_name'] ?? null,
                'service_charge_percentage' => $validated['service_charge_percentage'] ?? 0,
                'gst_enabled' => $validated['gst_enabled'] ?? false,
                'gst_on_service' => $validated['gst_on_service'] ?? false,
                'currency' => $validated['currency'] ?? 'MVR',
            ]);

            // Delete old participants, items, shared items
            $bill->participants()->delete();
            $bill->items()->delete();
            $bill->sharedItems()->delete();

            // Recreate participants
            foreach ($validated['participants'] as $participantData) {
                $bill->participants()->create([
                    'name' => $participantData['name'],
                ]);
            }

            // Recreate items
            if (isset($validated['items'])) {
                foreach ($validated['items'] as $itemData) {
                    $bill->items()->create([
                        'name' => $itemData['name'],
                        'price' => $itemData['price'],
                        'assigned_to' => $itemData['assigned_to'] ?? [],
                    ]);
                }
            }

            // Recreate shared items
            if (isset($validated['shared_items'])) {
                foreach ($validated['shared_items'] as $sharedItemData) {
                    $bill->sharedItems()->create([
                        'name' => $sharedItemData['name'],
                        'price' => $sharedItemData['price'],
                    ]);
                }
            }

            // Recalculate totals
            $this->calculateBillTotals($bill);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('rattehin.show', $bill)
                ]);
            }

            return redirect()->route('rattehin.show', $bill)
                ->with('success', 'Bill updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update bill: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Failed to update bill: ' . $e->getMessage());
        }
    }

    public function destroy(Bill $bill)
    {
        if ($bill->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this bill.');
        }

        $bill->delete();

        return redirect()->route('rattehin.index')
            ->with('success', 'Bill deleted successfully!');
    }

    /**
     * Handle OCR scanning of bill images
     */
    public function scanBill(Request $request)
    {
        $request->validate([
            'image' => 'required|string', // base64 image data in format: data:image/jpeg;base64,<data>
        ]);

        try {
            // Call OCR API (using OCR.Space API)
            $apiKey = config('services.ocr_space.api_key');

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'error' => 'OCR service is not configured. Please contact administrator.'
                ], 500);
            }

            // OCR.Space API expects full data URL format: data:image/jpeg;base64,<base64_data>
            // The 1024 KB limit applies to the base64 content, not the full data URL
            $imageData = $request->image;
            
            // Ensure we have the proper format - if it doesn't start with 'data:', add it
            if (strpos($imageData, 'data:') !== 0) {
                // If it's just base64, wrap it in data URL format
                $imageData = 'data:image/jpeg;base64,' . $imageData;
            }

            // Enhanced OCR parameters for better receipt recognition
            // OCR.Space API expects full data URL format: data:<content_type>;base64,<base64_data>
            $response = Http::asMultipart()->post('https://api.ocr.space/parse/image', [
                [
                    'name' => 'apikey',
                    'contents' => $apiKey
                ],
                [
                    'name' => 'language',
                    'contents' => 'eng'
                ],
                [
                    'name' => 'isOverlayRequired',
                    'contents' => 'false'
                ],
                [
                    'name' => 'base64Image',
                    'contents' => $imageData  // Send full data URL format: data:image/jpeg;base64,<data>
                ],
                [
                    'name' => 'OCREngine',
                    'contents' => '2'  // Engine 2 is better for receipts and structured documents
                ],
                [
                    'name' => 'scale',
                    'contents' => 'true'  // Auto-scale image for better accuracy
                ],
                [
                    'name' => 'isTable',
                    'contents' => 'true'  // Better for tabular data like bills
                ],
                [
                    'name' => 'detectOrientation',
                    'contents' => 'true'  // Auto-detect and correct image orientation
                ]
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'error' => 'OCR service failed to process the image',
                    'debug' => $response->body()
                ], 500);
            }

            $result = $response->json();

            if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['ErrorMessage'] ?? 'Failed to process image',
                    'debug' => $result
                ], 422);
            }

            // Check if ParsedResults exists and has data
            if (!isset($result['ParsedResults']) || empty($result['ParsedResults'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'No text could be extracted from the image. Please ensure the image is clear and contains readable text.',
                    'debug' => $result
                ], 422);
            }

            $extractedText = $result['ParsedResults'][0]['ParsedText'] ?? '';

            // Log the raw extracted text for debugging
            \Log::info('OCR Extracted Text:', ['text' => $extractedText]);

            // Parse the extracted text to identify items
            $parsedItems = $this->parseBillText($extractedText);

            // Log parsed items for debugging
            \Log::info('OCR Parsed Items:', ['items' => $parsedItems]);

            return response()->json([
                'success' => true,
                'extractedText' => $extractedText,
                'items' => $parsedItems,
                'rawOcrResult' => $result,  // Include full OCR response for debugging
            ]);

        } catch (\Exception $e) {
            \Log::error('OCR Scan Error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to scan bill: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate bill totals and update participants
     */
    private function calculateBillTotals(Bill $bill)
    {
        $GST_RATE = 0.08;
        $serviceChargePercentage = $bill->service_charge_percentage / 100;
        $numParticipants = $bill->participants->count();

        if ($numParticipants === 0) {
            return;
        }

        // Calculate subtotal from items
        $itemsSubtotal = $bill->items->sum('price');
        $sharedSubtotal = $bill->sharedItems->sum('price');
        $subtotal = $itemsSubtotal + $sharedSubtotal;

        // Service charge
        $serviceChargeAmount = $subtotal * $serviceChargePercentage;

        // GST calculation
        $gstBase = $bill->gst_enabled ? $subtotal : 0;
        if ($bill->gst_enabled && $bill->gst_on_service) {
            $gstBase += $serviceChargeAmount;
        }
        $gstAmount = $gstBase * $GST_RATE;

        // Grand total
        $grandTotal = $subtotal + $serviceChargeAmount + $gstAmount;

        // Update bill totals
        $bill->update([
            'subtotal' => $subtotal,
            'service_charge_amount' => $serviceChargeAmount,
            'gst_amount' => $gstAmount,
            'grand_total' => $grandTotal,
        ]);

        // Calculate per-participant amounts
        $sharedPerPerson = $sharedSubtotal / $numParticipants;

        foreach ($bill->participants as $participant) {
            // Calculate personal items for this participant
            $personalItemsTotal = 0;
            foreach ($bill->items as $item) {
                $assignedTo = $item->assigned_to ?? [];
                if (in_array($participant->name, $assignedTo)) {
                    $splitCount = count($assignedTo);
                    $personalItemsTotal += $item->price / $splitCount;
                }
            }

            // Participant's share of subtotal
            $participantSubtotal = $personalItemsTotal + $sharedPerPerson;

            // Participant's share of service charge
            $participantServiceCharge = $participantSubtotal * $serviceChargePercentage;

            // Participant's share of GST
            $participantGSTBase = $bill->gst_enabled ? $participantSubtotal : 0;
            if ($bill->gst_enabled && $bill->gst_on_service) {
                $participantGSTBase += $participantServiceCharge;
            }
            $participantGST = $participantGSTBase * $GST_RATE;

            // Participant's total
            $participantTotal = $participantSubtotal + $participantServiceCharge + $participantGST;

            // Update participant
            $participant->update([
                'personal_items' => $personalItemsTotal,
                'shared_items' => $sharedPerPerson,
                'service_charge' => $participantServiceCharge,
                'gst' => $participantGST,
                'total_amount' => $participantTotal,
            ]);
        }
    }

    /**
     * Parse bill text to extract food & drink items with quantity and amount
     * Ignores headers, footers, service charges, taxes, etc.
     */
    private function parseBillText(string $text): array
    {
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        $items = [];

        // Exact match keywords to ignore (must be exact or very close match)
        $exactIgnorePatterns = [
            '/^subtotal/i',
            '/^sub[\s\-]?total/i',
            '/^service[\s\-]?charge/i',
            '/^sc\s+\d+%/i',  // SC 5%, SC 10%, etc.
            '/^sc\s*\d+%/i',  // SC5%, SC10%, etc.
            '/^tax/i',
            '/^gst/i',
            '/^vat/i',
            '/^grand[\s\-]?total/i',
            '/^net[\s\-]?total/i',
            '/^bill[\s\-]?total/i',
            '/^total[\s\-]?amount/i',
            '/^total\s*\(/i',  // Total (MVR)
            '/^amount[\s\-]?payable/i',
            '/^bill[\s\-]?no/i',
            '/^invoice/i',
            '/^receipt/i',
            '/^thank[\s]you/i',
            '/^cashier/i',
            '/^covers?:/i',
            '/^table[\s\-]?no/i',
            '/^date/i',
            '/^time/i',
            '/^payment/i',
            '/^change/i',
            '/^balance/i',
            '/^tender/i',
            '/^discount/i',
            '/^unsettled/i',
            '/^account\s*no/i',
            '/^account\s*name/i',
        ];

        foreach ($lines as $line) {
            // Skip empty lines
            if (empty($line) || strlen($line) < 2) {
                continue;
            }

            // Check if line exactly matches ignore patterns
            $shouldIgnore = false;
            foreach ($exactIgnorePatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $shouldIgnore = true;
                    break;
                }
            }

            if ($shouldIgnore) {
                continue;
            }

            // Skip lines that are just headers (no numbers)
            if (!preg_match('/\d/', $line)) {
                continue;
            }

            $matched = false;

            // Pattern 1: "Item Name: Qty pcs, Price T" (common restaurant format)
            // Example: "Nuta: 1 pcs, 10.19 T" or "Chicken Fried Rice: 1 pcs, 64.81 T"
            if (!$matched && preg_match('/^(.+?):\s*(\d+)\s*(?:pcs|pc|pcs\.|pc\.)?,?\s*(\d+(?:\.\d{1,2})?)\s*T?$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $quantity = (int) $matches[2];
                $amount = (float) $matches[3];

                if ($amount > 0 && $quantity > 0 && $quantity < 100) {
                    $items[] = [
                        'name' => $this->cleanItemName($name),
                        'price' => $amount,
                        'quantity' => $quantity,
                    ];
                    $matched = true;
                }
            }

            // Pattern 2: "Item Name Qty pcs Price" (without colon/comma)
            // Example: "Nuta 1 pcs 10.19" or "Chicken Fried Rice 1 pcs 64.81"
            if (!$matched && preg_match('/^(.+?)\s+(\d+)\s+(?:pcs|pc|pcs\.|pc\.)\s+(\d+(?:\.\d{1,2})?)\s*T?$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $quantity = (int) $matches[2];
                $amount = (float) $matches[3];

                if ($amount > 0 && $quantity > 0 && $quantity < 100) {
                    $items[] = [
                        'name' => $this->cleanItemName($name),
                        'price' => $amount,
                        'quantity' => $quantity,
                    ];
                    $matched = true;
                }
            }

            // Pattern 3: "Item Name Qty * Amount" or "Item Name Qty # Amount"
            // Example: "Papad 5 * 75.00" or "Beer Bottle 5 # 300.00"
            if (!$matched && preg_match('/^(.+?)\s+(\d+)\s*[*#@×]\s*(\d+(?:\.\d{1,2})?)$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $quantity = (int) $matches[2];
                $amount = (float) $matches[3];

                if ($amount > 0 && $quantity > 0 && $quantity < 100) {
                    $items[] = [
                        'name' => $this->cleanItemName($name),
                        'price' => $amount,
                        'quantity' => $quantity,
                    ];
                    $matched = true;
                }
            }

            // Pattern 4: "Qty x Item Name Amount" (quantity first)
            // Example: "2 x Fried Rice 180.00"
            if (!$matched && preg_match('/^(\d+)\s*[x×]\s*(.+?)\s+(\d+(?:\.\d{1,2})?)$/i', $line, $matches)) {
                $quantity = (int) $matches[1];
                $name = trim($matches[2]);
                $amount = (float) $matches[3];

                if ($amount > 0 && $quantity > 0 && $quantity < 100) {
                    $items[] = [
                        'name' => $this->cleanItemName($name),
                        'price' => $amount,
                        'quantity' => $quantity,
                    ];
                    $matched = true;
                }
            }

            // Pattern 5: "Item Name Qty Amount" (space-separated, 3 parts)
            // Example: "Chicken Curry 2 180.00"
            if (!$matched && preg_match('/^(.+?)\s+(\d{1,2})\s+(\d+(?:\.\d{1,2})?)$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $quantity = (int) $matches[2];
                $amount = (float) $matches[3];

                // Only if quantity looks reasonable and amount > 1
                if ($amount >= 1 && $quantity > 0 && $quantity <= 50) {
                    $items[] = [
                        'name' => $this->cleanItemName($name),
                        'price' => $amount,
                        'quantity' => $quantity,
                    ];
                    $matched = true;
                }
            }

            // Pattern 5b: "Item Name Qty Amount" with currency (more flexible)
            // Example: "Chicken Curry 2 MVR 180.00" or "Item 3 45.50"
            if (!$matched && preg_match('/^(.+?)\s+(\d{1,2})\s+(?:MVR|MRF|Rf|Rs|RF)?\s*(\d+(?:\.\d{1,2})?)$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $quantity = (int) $matches[2];
                $amount = (float) $matches[3];

                if ($amount >= 1 && $quantity > 0 && $quantity <= 50 && !preg_match('/^\d+$/', $name)) {
                    $items[] = [
                        'name' => $this->cleanItemName($name),
                        'price' => $amount,
                        'quantity' => $quantity,
                    ];
                    $matched = true;
                }
            }

            // Pattern 6: "Item Name Amount" (just item and price, assume qty = 1)
            // Example: "Naan 45.00" or "Coffee MVR 35.00"
            if (!$matched && preg_match('/^(.+?)\s+(?:MVR|MRF|Rf|Rs|RF)?\s*(\d+(?:\.\d{1,2})?)$/i', $line, $matches)) {
                $name = trim($matches[1]);
                $amount = (float) $matches[2];

                // Only if name is text (not just numbers) and amount is reasonable
                if ($amount >= 1 && !preg_match('/^\d+$/', $name)) {
                    $items[] = [
                        'name' => $this->cleanItemName($name),
                        'price' => $amount,
                        'quantity' => 1,
                    ];
                    $matched = true;
                }
            }
        }

        // Post-process: Remove likely duplicates and totals
        $filteredItems = [];
        $totalAmount = 0;

        foreach ($items as $item) {
            $totalAmount += $item['price'];
        }

        foreach ($items as $item) {
            // Skip if item name contains total/subtotal keywords
            if (preg_match('/\b(total|subtotal|sub[\s\-]?total|amount|payable|charge|tax|gst|vat)\b/i', $item['name'])) {
                continue;
            }

            // Skip if this item's price equals the total (likely the grand total line)
            if (count($items) > 1 && abs($item['price'] - $totalAmount) < 0.01) {
                continue;
            }

            // Add to filtered items
            $filteredItems[] = $item;
        }

        return $filteredItems;
    }

    /**
     * Clean item name by removing extra symbols and normalizing
     */
    private function cleanItemName(string $name): string
    {
        // Remove leading/trailing symbols
        $name = preg_replace('/^[*#@\-\.\s]+/', '', $name);
        $name = preg_replace('/[*#@\-\s]+$/', '', $name);

        // Remove multiple spaces
        $name = preg_replace('/\s+/', ' ', $name);

        // Capitalize first letter of each word
        $name = ucwords(strtolower(trim($name)));

        return $name;
    }
}
