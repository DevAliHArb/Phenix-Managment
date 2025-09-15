<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VerificationController extends Controller
{

    /**
     * Show the email verification notice.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    // public function show($user)
    // {
    //     // This method is used to display the email verification notice.
    //     // You can customize it according to your application's needs.
    //     return view('auth.verify');
    // }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {

        // Find the user by the provided ID
        $user = User::findOrFail($request->id);

        // Determine the redirect URL based on the user's type
        $redirectUrl = '';
        switch ($user->type) {
            case 'template':
                $redirectUrl = 'https://template-b27.pages.dev/login';
                break;
            case 'bookshop':
                $redirectUrl = 'https://bookshopwebsite.pages.dev/login';
                break;
            case 'albouraq':
                $redirectUrl = 'https://albouraq-website.pages.dev/login';
                break;
            case 'sofiaco':
                $redirectUrl = 'https://sofiaco-website.pages.dev/login';
                break;
            case 'maktabox':
                $redirectUrl = 'https://mektabox-website.pages.dev/login';
                break;
            // Add more cases for other user types if needed
            default:
                // Set a default redirect URL if the user type is not recognized
                $redirectUrl = 'https://example.com/login';
        }

        // Check if the hash matches the user's email verification hash
        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return redirect($redirectUrl)->withErrors(['verification' => 'Invalid verification link']);
        }

        // Check if the user is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect($redirectUrl)->with('status', 'Your email address is already verified.');
        }

        // Mark the user's email as verified
        $user->markEmailAsVerified();

        // Redirect the user to the login page with a success message
        return redirect($redirectUrl)->with('status', 'Your email address has been verified. You can now log in.');
    }

    public function resend(Request $request)
{
    $user = User::where('email', $request->email)->where('type', $request->type)->first();
    $redirectUrl = '';
        switch ($request->type) {
            case 'template':
                $redirectUrl = 'https://template-b27.pages.dev/login';
                break;
            case 'bookshop':
                $redirectUrl = 'https://bookshopwebsite.pages.dev/login';
                break;
            case 'albouraq':
                $redirectUrl = 'https://albouraq-website.pages.dev/login';
                break;
            case 'sofiaco':
                $redirectUrl = 'https://sofiaco-website.pages.dev/login';
                break;
            // Add more cases for other user types if needed
            default:
                // Set a default redirect URL if the user type is not recognized
                $redirectUrl = 'https://example.com/login';
        }
    if ($user){
        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => 'User is already verified.',
            ], 201);
        }
    
        $user->sendEmailVerificationNotification();
    
        return response()->json([
            'success' => 'Email sent!',
        ], 201);
    } else {    
        response()->json([
            'error' => 'user not found.',
        ], 201);
    } 
}

}
