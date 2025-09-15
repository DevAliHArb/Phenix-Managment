<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use App\Models\User;
use App\Models\UserPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Stripe\StripeClient;
use Stripe\Stripe;


class UserPaymentController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user)
    {

        $payments = $user->userPayments;


        if ($payments->isEmpty()) {
            return $this->errorResponse('You have no available payment cards.', 404);
        }

        return $this->showAll($payments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, User $user)
    {
        $rules = [
            'card_type' => 'required|string',
            'card_number' => 'required|string',
            'month' => 'required|string',
            'year' => 'required|string',
            'cvv' => 'required|string',
        ];

        $this->validate($request, $rules);

        try {


            $stripe = new StripeClient(env('STRIPE_SECRET'));

            $existingCard = UserPayment::where('user_id', $user->id)->where('card_number', $request->get('card_number'))->first();

            if ($existingCard) {
                if ($existingCard->month == $request->get('month') && $existingCard->year == $request->get('year')) {
                    return response()->json('This card already exists');
                } else {
                    $existingCard->update([
                        'month' => $request->get('month'),
                        'year' => $request->get('year')
                    ]);

                    $paymentMethod = $stripe->paymentMethods->retrieve($existingCard->token);
                    $paymentMethod->card = [
                        'exp_month' => $request->get('month'),
                        'exp_year' => $request->get('year'),
                    ];

                    $paymentMethod->save();

                    return response()->json('Your Card is updated.');
                }
            } else {

                $cardNumber = $request->get('card_number');
                $cardType = '';

                if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $cardNumber)) {
                    $cardType = 'Visa';
                } elseif (preg_match('/^5[1-5][0-9]{14}$/', $cardNumber) || preg_match('/^2[2-7][0-9]{14}$/', $cardNumber)) {
                    $cardType = 'Master';
                } elseif (preg_match('/^3[47][0-9]{13}$/', $cardNumber)) {
                    $cardType = 'American Express';
                } elseif (preg_match('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $cardNumber)) {
                    $cardType = 'Discover';
                }
                if ($cardType != $request->get('card_type')) {
                    return response()->json(['error' => "Your card type is not correct"], 500);
                }

                $paymentMethod = $stripe->paymentMethods->create([
                    'type' => 'card',
                    'card' => [
                        'number' => $request->get('card_number'),
                        'exp_month' => $request->get('month'),
                        'exp_year' => $request->get('year'),
                        'cvc' => $request->get('cvv'),
                    ],
                ]);

                if (!$user->stripe_customer_id) {

                    $customer = $stripe->customers->create([
                        'payment_method' => $paymentMethod->id,
                    ]);

                    $user->stripe_customer_id = $customer->id;
                    $user->save();
                }


                if ($paymentMethod->customer == null) {
                    $stripe->paymentMethods->attach(
                        $paymentMethod->id,
                        ['customer' => $user->stripe_customer_id]
                    );
                }

                $data = $request->all();
                $data['user_id'] = $user->id;
                $data['card_number'] = $request->get('card_number');

                // Check if the user already has any payments
                if ($user->userPayments()->count() == 0) {
                    // Set the default value of the new address to true
                    $data['default'] = UserPayment::Default_PAYMENT;
                } else {
                    // Set the default value of the new payment to false
                    $data['default'] = UserPayment::NOT_DEFAULT_PAYMENT;
                }

                $data['token'] = $paymentMethod->id;

                $userpayment = UserPayment::create($data);
                return $userpayment;
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {

            $errorDetails = $e->getError()->toArray();
            // Extracting specific error details 

            $declineCode = $errorDetails['decline_code'];

            // Handle Stripe errors
            return response()->json(['error' => $declineCode . "-" . $e->getMessage()], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, UserPayment $payment)
    {
        $payment = UserPayment::where('user_id', $user->id)
            ->where('id', $payment->id)
            ->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment card not found'], 404);
        }

        return $this->showOne($payment);
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, User $user, UserPayment $payment)
    {
        $rules = [
            'default' => 'in:' . UserPayment::Default_PAYMENT . ',' . UserPayment::NOT_DEFAULT_PAYMENT,
        ];

        $this->validate($request, $rules);

        if ($request->default == UserPayment::Default_PAYMENT) {
            // Set the default value of the selected address to true
            $payment->default = UserPayment::Default_PAYMENT;
            $payment->save();

            // Update the default value of all other addresses belonging to the same user to false
            $user->userPayments()->where('id', '!=', $payment->id)->update(['default' => UserPayment::NOT_DEFAULT_PAYMENT]);
        } else {
            // If the default value is being changed to false, simply update the address without affecting other addresses
            $payment->fill($request->only(['card_type', 'card_number', 'year', 'month']));

            // Bcrypt the CVV
            $payment->cvv = Hash::make($request->cvv);

            if ($payment->isClean()) {
                return $this->errorResponse('You need to specify a different value to update', 422);
            }

            $payment->save();
        }

        return $this->showOne($payment);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, UserPayment $payment)
    {
        $this->checkAddress($user, $payment);
        $payment->delete();

        return $this->showOne($payment);
    }

    protected function checkAddress(User $user, UserPayment $payment)
    {
        if ($user->id != $payment->user_id) {
            throw new HttpException(422, 'The Specified user is not the actual user of the payment card');
        }
    }
}
