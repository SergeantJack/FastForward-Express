<?php
namespace App\Http\Repos;

use App\Bill;
use DB;

class BillRepo {

	public function ListAll() {
		$bills = Bill::All();

		return $bills;
	}

    public function GetById($id) {
	    $bill = Bill::where('bill_id', '=', $id)->first();

	    return $bill;
    }

    public function GetByInvoiceId($id) {
        $invoiceRepo = new InvoiceRepo();
        $accountRepo = new AccountRepo();

        $account_id = $invoiceRepo->GetById($id)->account_id;

        $sort_options = $invoiceRepo->GetSortOrderById($account_id);
        $subtotal_by = $invoiceRepo->GetSubtotalById($account_id);

        $bill_query = Bill::where('invoice_id', $id)
                ->join('addresses as pickup', 'pickup.address_id', '=', 'bills.pickup_address_id')
                ->join('addresses as delivery', 'delivery.address_id', '=', 'bills.delivery_address_id')
                ->join('accounts', 'accounts.account_id', '=', 'bills.charge_account_id')
                ->select('bill_id',
                DB::raw('format(amount + case when interliner_amount != NULL then interliner_amount else 0 end, 2) as amount'),
                'bill_number',
                'date',
                'charge_account_id',
                'charge_reference_value',
                'pickup.name as pickup_address_name',
                'delivery.name as delivery_address_name',
                'accounts.name as charge_account_name');

        $bills = array();
        if($subtotal_by == NULL) {
            foreach($sort_options as $option) {
                $bill_query->orderBy($option->database_field_name);
            }

            $bills[0] = new \stdClass();
            $bills[0]->bills = $bill_query->get();
        } else {
            $subtotal_ids = Bill::where('invoice_id', $id)->groupBy($subtotal_by->database_field_name)->pluck($subtotal_by->database_field_name);

            foreach($subtotal_ids as $subtotal_id) {
                $subtotal_query = clone $bill_query;
                $subtotal_query->where($subtotal_by->database_field_name, $subtotal_id);
                if($subtotal_by->database_field_name == 'charge_account_id') {
                    $sort_options = $invoiceRepo->GetSortOrderById($subtotal_id);
                    $subtotal_string = $subtotal_by->friendly_name . ' ' . $accountRepo->GetById($subtotal_id)->name;
                } else if($subtotal_by->database_field_name == 'charge_reference_value') {
                    $subtotal_string = $accountRepo->GetById($account_id)->custom_field . ' ' . $subtotal_id;
                } else
                    $subtotal_string = $subtotal_by->friendly_name . ' ' . $subtotal_id;
                foreach($sort_options as $option) {
                    $subtotal_query->orderBy($option->database_field_name);
                }
                $bills[$subtotal_string] = new \stdClass();
                $bills[$subtotal_string]->bills = $subtotal_query->get();
            }
        }

        return $bills;
    }

    public function CheckIfInvoiced($id) {
        $bill = Bill::where('bill_id', '=', $id)->first();
        
        return ($bill->is_invoiced);
    }

    public function CheckIfManifested($id) {
        $bill = Bill::where('bill_id', '=', $id)->first();

        if($bill->is_pickup_manifested)
            return true;
        else if($bill->is_delivery_manifested)
            return true;
        else
            return false;
    }

    public function Insert($bill) {
    	$new = new Bill;

    	return ($new->create($bill));
    }

    public function Delete($id) {
        $bill = $this->GetById($id);

        $bill->delete();
        return;
    }

    public function Update($bill) {
        $old = $this->GetById($bill['bill_id']);

        $old->charge_account_id = $bill['charge_account_id'];
        $old->pickup_account_id = $bill['pickup_account_id'];
        $old->delivery_account_id = $bill['delivery_account_id'];
        $old->pickup_address_id = $bill['pickup_address_id'];
        $old->delivery_address_id = $bill['delivery_address_id'];
        $old->charge_reference_value = $bill['charge_reference_value'];
        $old->pickup_reference_value = $bill['pickup_reference_value'];
        $old->delivery_reference_value = $bill['delivery_reference_value'];
        $old->pickup_driver_id = $bill['pickup_driver_id'];
        $old->delivery_driver_id = $bill['delivery_driver_id'];
        $old->pickup_driver_commission = $bill['pickup_driver_commission'];
        $old->delivery_driver_commission = $bill['delivery_driver_commission'];
        $old->interliner_id = $bill['interliner_id'];
        $old->interliner_amount = $bill['interliner_amount'];
        $old->skip_invoicing = $bill['skip_invoicing'];
        $old->bill_number = $bill['bill_number'];
        $old->description = $bill['description'];
        $old->date = $bill['date'];
        $old->amount = $bill['amount'];
        $old->delivery_type = $bill['delivery_type'];

        $old->save();

        return $old;
    }

    public function CountByInvoiceId($invoiceId) {
        $bills = \DB::table("bills")->select(\DB::raw('count(bill_id) as bill_count'))
            ->where('invoice_id', '=', $invoiceId)
            ->get();

        return $bills[0]->bill_count;
    }

    public function CountByManifestId($manifest_id) {
        $count = Bill::where('pickup_manifest_id', $manifest_id)
                ->orWhere('delivery_manifest_id', $manifest_id)
                ->count();

        return $count;
    }

    public function GetByManifestId($manifest_id) {
        $pickup_bills = Bill::where('pickup_manifest_id', $manifest_id)
            ->join('accounts', 'accounts.account_id', '=', 'bills.charge_account_id')
            ->select('bill_id', 'bill_number', 'date', DB::raw('format(amount, 2)'), 'charge_account_id', 'accounts.name as account_name', 'delivery_type', DB::raw('"Pickup" as type'), DB::raw('format(round((amount * pickup_driver_commission), 2), 2) as driver_income'));

        $bills = Bill::where('delivery_manifest_id', $manifest_id)
            ->join('accounts', 'accounts.account_id', '=', 'bills.charge_account_id')
            ->select('bill_id', 'bill_number', 'date', 'amount', 'charge_account_id', 'accounts.name as account_name', 'delivery_type', DB::raw('"Delivery" as type'), DB::raw('format(round((amount * delivery_driver_commission), 2), 2) as driver_income'))
            ->union($pickup_bills)
            ->orderBy('date')
            ->orderBy('bill_id')
            ->get();

        return $bills;
    }

    public function GetManifestOverviewById($manifest_id) {
        $bills = Bill::where('pickup_manifest_id', $manifest_id)
            ->orWhere('delivery_manifest_id', $manifest_id)
            ->select('date',
                    DB::raw('format(sum(case when pickup_manifest_id = ' . $manifest_id . ' then round(amount * pickup_driver_commission, 2) else 0 end), 2) as pickup_amount'),
                    DB::raw('format(sum(case when delivery_manifest_id = ' . $manifest_id . ' then round(amount * delivery_driver_commission, 2) else 0 end), 2) as delivery_amount'),
                    DB::raw('count(case when pickup_manifest_id = ' . $manifest_id . ' then 1 else NULL end) as pickup_count'),
                    DB::raw('count(case when delivery_manifest_id = ' . $manifest_id . ' then 1 else NULL end) as delivery_count'))
            ->groupBy('date')
            ->get();

        return $bills;
    }

    public function GetDriverTotalByManifestId($manifest_id) {
        $total = Bill::where('pickup_manifest_id', $manifest_id)
                ->orWhere('delivery_manifest_id', $manifest_id)
                ->select(DB::raw('sum(case ' .
                    'when pickup_manifest_id = ' . $manifest_id . ' and delivery_manifest_id = ' . $manifest_id . ' then round(amount * (pickup_driver_commission + delivery_driver_commission), 2) ' .
                    'when pickup_manifest_id = ' . $manifest_id . ' then round(amount * pickup_driver_commission, 2) ' .
                    'when delivery_manifest_id  = ' . $manifest_id . ' then round(amount * delivery_driver_commission, 2) end) as total'))
                ->pluck('total');

        return $total[0];
    }

    public function CountByDriverBetweenDates($driver_id, $start_date, $end_date) {
        $count = Bill::whereDate('date', '>=', $start_date)
                ->whereDate('date', '<=', $end_date)
                ->where(function($query) use ($driver_id) {
                    $query->where('pickup_driver_id', '=', $driver_id)
                    ->where('is_pickup_manifested', false)
                    ->orWhere('delivery_driver_id', '=', $driver_id)
                    ->where('is_delivery_manifested', false);
                })
                ->count();

        return $count;
    }

    public function CountByDriver($driverId) {
	    $count = Bill::where('pickup_driver_id', '=', $driverId)
            ->orWhere('delivery_driver_id', '=', $driverId)
            ->count();

	    return $count;
    }

    public function GetInvoiceSubtotalByField($invoice_id, $field_name, $field_value) {
        $subtotal = Bill::where('invoice_id', $invoice_id)
            ->where($field_name, $field_value)
            ->value(DB::raw('sum(amount + case when interliner_amount != NULL then interliner_amount else 0 end)'));

        return $subtotal;
    }

    public function GetAmountByInvoiceId($invoice_id) {
        $amount = Bill::where('invoice_id', $invoice_id)
            ->value(DB::raw('format(sum(amount + case when interliner_amount != NULL then interliner_amount else 0 end), 2)'));

        return $amount;
    }
}
