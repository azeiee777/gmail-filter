<?php

namespace App\Http\Controllers;

use App\Services\GmailService;
use Illuminate\Http\Request;

class GmailController extends Controller
{
    protected $gmailService;

    public function __construct(GmailService $gmailService)
    {
        $this->gmailService = $gmailService;
    }

    // Redirect to Google OAuth consent screen
    public function redirectToGoogle()
    {
        $authUrl = $this->gmailService->getAuthUrl();
        return redirect()->away($authUrl);
    }

    // Handle the OAuth callback from Google
    public function handleGoogleCallback(Request $request)
    {
        $authCode = $request->input('code');

        // Authenticate and retrieve access token
        $accessToken = $this->gmailService->authenticate($authCode);

        // Store access token in session
        session(['access_token' => $accessToken['access_token']]);

        // List messages using the access token
        $messages = $this->gmailService->listMessages(session('access_token'));
        
        return response()->json($messages);
    }
}
