<?php

namespace RicLeP\SparkSinglePayment;

use Exception;
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
	public function __construct($user)
	{
		$this->user = $user;
	}

	/**
	 * Make the payment
	 *
	 * return array
	 * @throws \Exception
	 */
	public function charge($description, $amount, $options = [])
	{
		$response = $this->individualOrTeam()->invoiceFor($description, $amount, $options);

		if ($response->success) {
			$invoice = new Invoice($this->individualOrTeam(), $response->transaction);

			app(LocalInvoiceRepository::class)->createForTeam(
				$this->individualOrTeam(), $invoice
			);

			$this->sendInvoiceNotification($this->individualOrTeam(), $invoice);

			return $response;
		}

		throw new Exception('Provider was unable to perform a charge: '.$response->message);
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