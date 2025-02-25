<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Mail\ValidatorEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Mail\CodeEmail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

/**
 * AuthController handles authentication, user activation, and 2FA processes.
 */
class AuthController extends Controller
{
    /**
     * Constructor for AuthController.
     * Applies middleware to ensure API authentication except for specified routes.
     */
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => ['login', 'register', 'activate', 'verify2FACode', 'generateAndSend2FACode']
        ]);
    }

    /**
     * Handles user login and initiates 2FA if the user is active.
     *
     * @param Request $request Incoming HTTP request.
     * @return RedirectResponse Redirects to 2FA view or back to login with an error.
     */
    public function login(Request $request): RedirectResponse
    {
        $rules = [
            'password' => [
                'required',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
            'g-recaptcha-response' => 'required',
             'email' => 'required|email'
         ];
         // Crear validador
         $validator = Validator::make($request->all(), $rules);
     
         // Si la validación falla, redirigir con errores y datos ingresados
         if ($validator->fails()) {
            //dd($validator->errors()->all()); 
            return redirect()->back()->with('error',$validator->errors());
         }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response')
        ]);
        $recaptchaData = $response->json();

        if (!$recaptchaData['success']) {
            return redirect()->back()->with('error', 'VALIDATION_003-Failed reCAPTCHA verification.');
        }   

        // Validate login credentials
        $credentials = $request->only(['email', 'password']);
    

        if (!auth()->attempt($credentials)) {
            return redirect()->back()->with('error', 'VALIDATION_001-Incorrect username or password.');
        }

        // Retrieve authenticated user
        $user = auth()->user();
        $user = User::find($user->id);

        // Check if the user account is active
        if (!$user || !$user->is_active) {
            return redirect()->back()->with('error', 'VALIDATION_002-Your account is not active, check your email.');
        }

        // Store encrypted user ID in session for 2FA
        session(['2fa_user_id' => encrypt($user->id)]);

        // Generate and send 2FA code
        $this->generateAndSend2FACode($user);

        return redirect('/auth/verifycode')->with('success', 'A code has been sent to your email.');

    }

    /**
     * Generates a 2FA code, saves it to the database, and sends it to the user.
     *
     * @param User $user The user model for whom the code is generated.
     */
    private function generateAndSend2FACode(User $user)
    {
        // Generate a 6-digit code
        $code = mt_rand(100000, 999999);

        // Hash and store the code with an expiration time
        $encryptedCode = Hash::make($code);
        $user->two_factor_code = $encryptedCode;
        $user->two_factor_expires_at = now()->addMinutes(10);
        $user->save();

        // Send the code via email
        Mail::to($user->email)->send(new CodeEmail($code));
    }

    /**
     * Verifies the 2FA code provided by the user.
     *
     * @param Request $request Incoming HTTP request containing the 2FA code.
     * @return RedirectResponse Redirects to the appropriate view based on verification result.
     */
    public function verify2FACode(Request $request)
{
    // Validar código y reCAPTCHA
    $request->validate([
        'code' => 'required|numeric|digits:6',
        'g-recaptcha-response' => 'required'
    ]);

    Log::info('Starting 2FA code verification.');

    // Validar reCAPTCHA
    $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
        'secret' => config('services.recaptcha.secret_key'),
        'response' => $request->input('g-recaptcha-response')
    ]);

    $recaptchaData = $response->json();
    if (!$recaptchaData['success']) {
        return redirect()->route('2fa.view')->with('error', 'AUTH_003-Failed reCAPTCHA verification.');
    }

    try {
        $userId = decrypt(session('2fa_user_id'));
        Log::info("User ID decrypted successfully: $userId");
    } catch (\Exception $e) {
        Log::error('Error decrypting user ID: ' . $e->getMessage());
        return redirect()->route('login')->with('error', 'AUTH_002-Session expired. Please log in again.');
    }

    $user = User::find($userId);
    if (!$user) {
        return redirect()->route('login')->with('error', 'AUTH_003-User not found.');
    }

    if (!Hash::check($request->code, $user->two_factor_code)) {
        return redirect()->route('2fa.view')->with('error', 'AUTH_004-Incorrect code.');
    }

    if (now()->greaterThan($user->two_factor_expires_at)) {
        return redirect()->route('2fa.view')->with('error', 'AUTH_005-Code expired.');
    }

    Auth::login($user, true);
    $user->update(['two_factor_code' => null, 'two_factor_expires_at' => null]);
    session()->forget('2fa_user_id');

    return redirect()->route('welcome')->with('success', 'Authentication successful.');
}

    /**
     * Logs out the user and invalidates the JWT token.
     *
     * @return \Illuminate\Http\JsonResponse JSON response confirming logout.
     */
    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Logged out successfully'], 201);
    }

    /**
     * Refreshes the JWT token.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing the new token.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Constructs a JSON response with the JWT token and its metadata.
     *
     * @param string $token The JWT token.
     * @return \Illuminate\Http\JsonResponse JSON response with token data.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL()
        ]);
    }

    /**
     * Activates a user account using a signed URL.
     *
     * @param User $user The user model to activate.
     * @param Request $request The HTTP request containing the signed URL.
     * @return RedirectResponse Redirects to the activation view with success or error messages.
     */
    public function activate(User $user, Request $request)
    {
        // Validate the signed URL
        if (!$request->hasValidSignature()) {
            return redirect()->route('access.activation', ['user' => $user->id])
                ->with('error', 'AUTH_005-Invalid or expired URL.');
        }

        // Activate the user account
        $user->is_active = true;
        $user->save();

        return redirect()->route('access.activation', ['user' => $user->id])
            ->with('success', 'Account activated successfully.');
    }
}
