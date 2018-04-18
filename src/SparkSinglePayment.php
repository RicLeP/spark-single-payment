<?php

namespace RicLeP\SparkSinglePayment;

use Laravel\Cashier\Invoice;
use Laravel\Spark\Contracts\Repositories\LocalInvoiceRepository;
use Laravel\Spark\Http\Controllers\Settings\Billing\SendsInvoiceNotifications;


class SparkSinglePayment
{
	use SendsInvoiceNotifications;

	protected $user;
	protected $description;
	protected $amount;
	protected $options;

	/**
	 * Makes a single payment for the given user.
	 * @param $user
	 * @param $amount
	 * @param $description
	 */
	public function __construct($user, $description, $amount, $options = [])
	{
		$this->user = $user;
		$this->description = $description;
		$this->amount = $amount;
		$this->options = $options;

		$this->pay();
	}

	/**
	 * Make the payment
	 *
	 * @return mixed
	 */
	private function pay()
	{
		$response = $this->individualOrTeam()->invoiceFor($this->description, $this->amount, $this->options);

		if ($response->success) {
			$invoice = new Invoice($this->individualOrTeam(), $response->transaction);

			app(LocalInvoiceRepository::class)->createForTeam(
				$this->individualOrTeam(), $invoice
			);

			$this->sendInvoiceNotification($this->individualOrTeam(), $invoice);

			return true;
		}

		return $response;
	}

	/**
	 * Determines if we should invoice an individual or a team
	 *
	 * @return mixed
	 */
	private function individualOrTeam()
	{
		if ($this->user->hasTeams()) {
			return $this->user->currentTeam;
		}

		return $this->user;
	}
}