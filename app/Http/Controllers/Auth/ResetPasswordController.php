<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetPasswordController extends Controller
{
    // This method will display the password reset form
    public function showResetForm(Request $request, $token = null)
    {
        $email = $request->email;
        $type = $request->type;
        
        if ($type === 'bookshop') {
            $url = 'https://bookshopwebsite.pages.dev/resetpassword/' . '?token=' . $token . '&email=' . $email;
        } elseif ($type === 'albouraq') {
            $url = 'https://albouraq-website.pages.dev/reset-password/' . '?token=' . $token . '&email=' . $email;
        } elseif ($type === 'maktabox') {
            $url = 'https://mektabox-website.pages.dev/reset-password/' . '?token=' . $token . '&email=' . $email;
        } elseif ($type === 'template') {
            $url = 'https://template-b27.pages.dev/reset-password/' . '?token=' . $token . '&email=' . $email;
        } else {
            // Handle other types or invalid types here
            return response()->json(['error' => 'Invalid type'], 400);
        }
        
        return Redirect::to($url);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate($this->rules());

        // Find the user based on email and type
        $user = User::where('email', $request->email)->where('type', $request->type)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        // Reset the password for the user
        $this->resetUserPassword($user, $request->password);

        // Return response based on password reset status
        return $this->sendResetResponse($request, Password::PASSWORD_RESET);
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * Reset the password for the specified user.
     *
     * @param  \App\Models\User  $user
     * @param  string  $password
     * @return void
     */
    protected function resetUserPassword(User $user, $password)
    {
        $user->password = Hash::make($password);
        $user->setRememberToken(Str::random(60));
        $user->save();
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            return new JsonResponse(['message' => trans($response)], 200);
        }

        return new JsonResponse(['status' => trans($response)], 200);
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages([
                'email' => [trans($response)],
            ]);
        }

        return redirect()->back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }
}
