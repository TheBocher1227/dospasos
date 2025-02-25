<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\ValidatorEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rules\Password;

/**
 * UsersController handles user-related operations such as registration,
 * activation, and managing signed routes for account verification.
 */
class UsersController extends Controller
{
    /**
     * Handles user registration and sends an activation email.
     *
     * @param Request $request HTTP request containing user input data.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the registration form with success or error messages.
     */
   

     public function store(Request $request)
     {
         // Definir reglas de validación
         $rules = [
            'password' => [
                'required',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
            'g-recaptcha-response' => 'required',
             'email' => 'required|email|unique:users,email'
         ];
         // Crear validador
         $validator = Validator::make($request->all(), $rules);
     
         // Si la validación falla, redirigir con errores y datos ingresados
         if ($validator->fails()) {
            //dd($validator->errors()->all()); 
            return redirect()->back()->with('error',$validator->errors());
         }
     
         // Validar reCAPTCHA
         $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
             'secret' => config('services.recaptcha.secret_key'),
             'response' => $request->input('g-recaptcha-response'),
             'remoteip' => $request->ip()
         ]);
     
         $recaptchaData = $response->json();
     
         if (!$recaptchaData['success']) {
             return back()->withErrors(['g-recaptcha-response' => 'AUTH_006 - reCAPTCHA validation failed.'])->withInput();
         }
     
         // Crear el usuario
         $user = User::create([
             'name' => $request->name,
             'email' => $request->email,
             'phonenumber' => $request->phonenumber,
             'password' => Hash::make($request->password),
             'is_active' => false,
         ]);
     
         // Generar ruta de activación de cuenta
         $signedRoute = URL::temporarySignedRoute(
             'activate',
             now()->addMinutes(30),
             ['user' => $user->id]
         );
     
         // Enviar correo de activación
         Mail::to($request->email)->send(new ValidatorEmail($signedRoute));
     
         return redirect()->route('register')->with('success', 'Usuario creado, revisa tu correo electrónico.');
     }
    /**
     * Activates a user account if the signed URL is valid.
     *
     * @param User $user The user model to activate.
     * @return \Illuminate\View\View Returns the email confirmation view.
     */
    public function activate(User $user)
    {
        // Activate the user account
        $user->is_active = true;
        $user->save();

        // Return a confirmation view
        return view('mails.confirmemail');
    }

    /**
     * Resends an activation email if the previous one expired.
     *
     * @param Request $request HTTP request containing the user ID.
     * @return \Illuminate\Http\RedirectResponse Redirects to login with success or error messages.
     */
    public function refreshSignedRoute(Request $request)
    {
        // Find the user by ID
        $user = User::find($request->user_id);

        // If the user does not exist, redirect with an error message
        if (!$user) {
            return redirect()->back()->with('error', 'AUTH_006-The user does not exist.');
        }

        // If the user is already active, redirect to login
        if ($user->is_active) {
            return redirect()->route('login')->with('success', 'The account is active, sign in.');
        }

        // Generate a new signed route for activation
        $signedRoute = URL::temporarySignedRoute(
            'activate',
            now()->addMinutes(30), // The URL is valid for 30 minutes
            ['user' => $user->id]
        );

        // Resend the activation email
        Mail::to($user->email)->send(new ValidatorEmail($signedRoute));

        // Redirect to login with a success message
        return redirect()->route('login')->with('success', 'A new activation link has been sent to your email.');
    }

    /**
     * Displays the activation view for a specific user.
     *
     * @param int $userId The ID of the user to activate.
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * Redirects with an error message if the user does not exist, otherwise shows the activation view.
     */
    public function showActivationView($userId)
    {
        // Find the user by ID
        $user = User::find($userId);

        // Redirect back if the user does not exist
        if (!$user) {
            return redirect()->route('register')->with('error', 'AUTH_006-The user does not exist');
        }

        // Return the activation view with the user ID
        return view('access.activation', ['userId' => $userId]);
    }
}
