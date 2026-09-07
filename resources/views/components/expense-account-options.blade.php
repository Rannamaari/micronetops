@props(['accounts', 'selected' => null])

<option value="">Select account</option>

@php
    $companyAccounts = $accounts->where('is_petty_cash', false);
    $staffAccounts = $accounts->where('is_petty_cash', true);
    $selectedId = (string) old('account_id', $selected);
@endphp

@if ($companyAccounts->isNotEmpty())
    <optgroup label="Company Accounts">
        @foreach ($companyAccounts as $account)
            <option value="{{ $account->id }}" @selected($selectedId === (string) $account->id)>
                {{ $account->name }} — MVR {{ number_format($account->balance, 2) }}
            </option>
        @endforeach
    </optgroup>
@endif

@if ($staffAccounts->isNotEmpty())
    <optgroup label="Staff Petty Cash">
        @foreach ($staffAccounts as $account)
            <option value="{{ $account->id }}" @selected($selectedId === (string) $account->id)>
                {{ $account->custodian?->name ?? $account->name }} — MVR {{ number_format($account->balance, 2) }}
            </option>
        @endforeach
    </optgroup>
@endif
