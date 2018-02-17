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
        $account_ids = Bill::where('invoice_id', $id)->groupBy('charge_account_id')->pluck('charge_account_id');
        $bills = array();
        foreach($account_ids as $account_id) {
            $bills_by_account = Bill::where('invoice_id', '=', $id)
            ->where('charge_account_id', $account_id)
            ->join('addresses as pickup', 'pickup.address_id', '=', 'bills.pickup_address_id')
            ->join('addresses as delivery', 'delivery.address_id', '=', 'bills.delivery_address_id')
            ->join('accounts', 'accounts.account_id', '=', 'bills.charge_account_id')
            ->select('bill_id',
            'amount',
            'interliner_amount',
            'bill_number',
            'date',
            'charge_account_id',
            'charge_reference_value',
            'pickup.name as pickup_address_name',
            'delivery.name as delivery_address_name',
            'accounts.name as charge_account_name');

			$sort_options = $invoiceRepo->GetSortOrderById($account_id);
            foreach($sort_options as $option) {
                $bills_by_account->orderBy($option->database_field_name);
            }

            $bills[$account_id] = $bills_by_account->get();
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
}
