<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Letter of Appointment - {{ $employee->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #1a1a1a;
            max-width: 21cm;
            margin: 0 auto;
            padding: 1.5cm;
            background: white;
        }

        .letterhead {
            border-bottom: 4px solid #1e40af;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            flex: 1;
        }

        .logo {
            width: 120px;
            height: auto;
        }

        .logo img {
            width: 100%;
            height: auto;
            display: block;
        }

        .company-info {
            flex: 2;
            text-align: right;
        }

        .company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
        }

        .company-address {
            font-size: 10pt;
            color: #4b5563;
            line-height: 1.4;
        }

        .letter-date {
            font-size: 11pt;
            color: #1e40af;
            font-weight: 600;
            margin-top: 10px;
        }

        .document-title {
            text-align: center;
            margin: 30px 0;
            padding: 15px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            border-radius: 8px;
        }

        .document-title h1 {
            font-size: 20pt;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-header {
            background: #eff6ff;
            border-left: 4px solid #1e40af;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-weight: bold;
            font-size: 12pt;
            color: #1e40af;
        }

        .field-row {
            margin-bottom: 10px;
            padding-left: 20px;
            display: flex;
            line-height: 1.6;
        }

        .field-label {
            font-weight: 600;
            min-width: 220px;
            color: #374151;
        }

        .field-value {
            flex: 1;
            color: #1a1a1a;
        }

        .signature-line {
            border-top: 2px solid #1e40af;
            margin-top: 60px;
            margin-bottom: 8px;
        }

        .signature-label {
            font-size: 10pt;
            color: #6b7280;
            font-weight: 600;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .toolbar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 11pt;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(30, 64, 175, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #1e40af;
            border: 2px solid #1e40af;
        }

        .btn-secondary:hover {
            background: #eff6ff;
        }
    </style>
</head>
<body>
    <div class="toolbar no-print">
        <button class="btn btn-secondary" onclick="window.close()">Close</button>
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print Letter</button>
    </div>

    <!-- Letterhead -->
    <div class="letterhead">
        <div class="logo-section">
            <div class="logo">
                <img src="{{ asset('logo.png') }}" alt="Micronet Logo">
            </div>
        </div>
        <div class="company-info">
            <div class="company-name">MICRONET</div>
            <div class="company-address">
                M. Ithaamuiyge 1, Alimasmagu<br>
                Male', Republic of Maldives<br>
                Email: sales@micronet.mv<br>
                Reg. No: SP-0733/2017
            </div>
            <div class="letter-date">
                Date: {{ $employee->hire_date ? $employee->hire_date->format('d F Y') : now()->format('d F Y') }}
            </div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        <h1>LETTER OF APPOINTMENT</h1>
    </div>

    <!-- Section 1: Employer Details -->
    <div class="section">
        <div class="section-header">1. Details of Employer</div>
        <div class="field-row">
            <span class="field-label">Name:</span>
            <span class="field-value">Micronet</span>
        </div>
        <div class="field-row">
            <span class="field-label">Address:</span>
            <span class="field-value">M. Ithaamuiyge 1, Alimasmagu, Male' Republic of Maldives</span>
        </div>
        <div class="field-row">
            <span class="field-label">Contact Details / Email:</span>
            <span class="field-value">sales@micronet.mv</span>
        </div>
        <div class="field-row">
            <span class="field-label">Country of origin:</span>
            <span class="field-value">Maldives</span>
        </div>
        <div class="field-row">
            <span class="field-label">Registration No.:</span>
            <span class="field-value">SP-0733/2017</span>
        </div>
    </div>

    <!-- Section 2: Employee Details -->
    <div class="section">
        <div class="section-header">2. Details of Employee</div>
        <div class="field-row">
            <span class="field-label">• Name:</span>
            <span class="field-value">{{ $employee->name }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Address:</span>
            <span class="field-value">{{ $employee->permanent_address ?: $employee->address ?: 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Nationality:</span>
            <span class="field-value">{{ $employee->nationality ?: 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Contact number:</span>
            <span class="field-value">{{ $employee->contact_number_home ?: $employee->phone }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Date of Birth:</span>
            <span class="field-value">{{ $employee->date_of_birth ? $employee->date_of_birth->format('d M Y') : 'N/A' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Passport Number:</span>
            <span class="field-value">{{ $employee->passport_number ?: 'N/A' }}</span>
        </div>
    </div>

    <!-- Section 3: Emergency Contact -->
    <div class="section">
        <div class="section-header">3. Emergency Contact Details</div>
        <div class="field-row">
            <span class="field-label">Name & Contact:</span>
            <span class="field-value">{{ $employee->emergency_contact_name ?: 'N/A' }}{{ $employee->emergency_contact_phone ? ' - ' . $employee->emergency_contact_phone : '' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">Relationship:</span>
            <span class="field-value">{{ $employee->emergency_contact_relationship ?: 'N/A' }}</span>
        </div>
    </div>

    <!-- Section 4: Employment Details -->
    <div class="section">
        <div class="section-header">4. Details of Employment</div>
        <div class="field-row">
            <span class="field-label">• Job Title / Occupation:</span>
            <span class="field-value">{{ $employee->position }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Work Type:</span>
            <span class="field-value">{{ ucfirst($employee->type) }}</span>
        </div>
        @if($employee->basic_salary_usd)
        <div class="field-row">
            <span class="field-label">• Basic Salary (USD):</span>
            <span class="field-value">${{ number_format($employee->basic_salary_usd, 2) }}</span>
        </div>
        @endif
        <div class="field-row">
            <span class="field-label">• Basic Salary (MVR):</span>
            <span class="field-value">{{ number_format($employee->basic_salary, 2) }} MVR</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Date of Salary payment:</span>
            <span class="field-value">10th of Every month</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Work site:</span>
            <span class="field-value">{{ $employee->work_site ?: 'Micro Moto H. Goldenmeet aage' }}</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Date of Commence:</span>
            <span class="field-value">{{ $employee->hire_date ? $employee->hire_date->format('d F Y') : 'N/A' }}</span>
        </div>
        @if($employee->job_description)
        <div class="field-row" style="display: block;">
            <span class="field-label">• Job Description:</span>
            <div style="padding-left: 20px; margin-top: 5px; white-space: pre-wrap;">{{ $employee->job_description }}</div>
        </div>
        @else
        <div class="field-row">
            <span class="field-label">• Job Description:</span>
            <span class="field-value">N/A</span>
        </div>
        @endif
        <div class="field-row">
            <span class="field-label">• Working Hours:</span>
            <span class="field-value">08:00 to 17:00</span>
        </div>
        <div class="field-row">
            <span class="field-label">• Work Status:</span>
            <span class="field-value">{{ ucfirst($employee->work_status ?: 'Permanent') }}</span>
        </div>
    </div>

    <!-- Section 5: Signatory Details -->
    <div class="section">
        <div class="section-header">5. Details of Signatory</div>
        <div class="field-row">
            <span class="field-label">Name:</span>
            <span class="field-value">Hussain Munad Ibrahim</span>
        </div>
        <div class="field-row">
            <span class="field-label">Designation:</span>
            <span class="field-value">Owner</span>
        </div>
    </div>

    <!-- Signature Section -->
    <div style="margin-top: 60px; text-align: right; max-width: 300px; margin-left: auto;">
        <div class="signature-line"></div>
        <div class="signature-label">Employer Signature</div>
        <div style="margin-top: 5px; font-size: 9pt; color: #9ca3af;">Hussain Munad Ibrahim (Owner)</div>
    </div>
</body>
</html>
