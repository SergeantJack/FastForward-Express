<?php
namespace App\Http\Validation;

class AccountValidationRules {
    public function GetValidationRules($validateAccountNumberUnique, $accountId, $accountNumber, $isSubLocation, $giveDiscount, $customField) {
        $rules = [
            'account-number' => 'required|unique:accounts,account_number,' . $accountId . ',account_id',
            'name' => 'required',
        ];

        $messages = [
            'account-number.required' => 'Account Number is required',
            'account-number.unique' => 'Account Number must be unique',
            'name.required' => 'Company Name is required.',
        ];

        if ($isSubLocation) {
            $rules = array_merge($rules, ['parent-account-id' => 'required']);
            $messages = array_merge($messages, ['parent-account-id.required' => 'A Parent Account is required.']);
        }

        if ($giveDiscount) {
            $rules = array_merge($rules, ['discount' => 'required|numeric']);
            $messages = array_merge($messages, [
                'discount.required' => 'A Discount value is required.',
                'discount.numeric' => 'Discount must be a number.'
            ]);
        }

        if ($customField) {
            $rules = array_merge($rules, ['custom-tracker' => 'required']);
            $messages = array_merge($messages, ['custom-tracker.required' => 'Tracking Field Name is required.']);
        }

        return [
            'rules' => $rules,
            'messages' => $messages
        ];
    }
}
