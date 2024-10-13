<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;

class GmailService
{
    protected $client;

    public function __construct()
    {
        // Initialize Google Client
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/credentials.json')); // Path to your credentials.json
        $this->client->addScope(Gmail::GMAIL_READONLY);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
    }

    // Generate the OAuth 2.0 authorization URL
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    // Authenticate and obtain an access token
    public function authenticate($authCode)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
        return $accessToken;
    }

    public function listMyMessages($accessToken)
    {
        return 10;
    }

    // List messages in the user's Gmail account
    public function listAllMessages($accessToken)
    {
        $this->client->setAccessToken($accessToken);

        // Check if the access token is expired
        if ($this->client->isAccessTokenExpired()) {
            // If you have a refresh token, refresh the access token
            // Uncomment the lines below if you're storing refresh tokens
            /*
            $refreshToken = '...'; // Retrieve this from your stored credentials
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            $accessToken = $this->client->getAccessToken();
            */
        }

        $gmailService = new Gmail($this->client);
        $messages = $gmailService->users_messages->listUsersMessages('me', ['maxResults' => 10]);
        return $messages;
    }

    public function listMessages($accessToken)
    {
        $this->client->setAccessToken($accessToken);

        if ($this->client->isAccessTokenExpired()) {
            // Handle token expiration, refresh if necessary
        }

        $gmailService = new Gmail($this->client);

        try {
            // Step 1: List messages
            $messagesResponse = $gmailService->users_messages->listUsersMessages('me', ['maxResults' => 10]);
            $messages = $messagesResponse->getMessages();

            $messageDetails = [];

            // Step 2: Loop through each message and get full details
            foreach ($messages as $message) {
                $messageId = $message->getId();
                
                // Fetch full message details
                $messageDetail = $gmailService->users_messages->get('me', $messageId);
                $payload = $messageDetail->getPayload();
                $headers = $payload->getHeaders();

                // Initialize variables for storing subject, date, etc.
                $subject = '';
                $date = '';
                $from = '';

                // Step 3: Extract headers (subject, date, from)
                foreach ($headers as $header) {
                    if ($header->getName() == 'Subject') {
                        $subject = $header->getValue();
                    }
                    if ($header->getName() == 'Date') {
                        $date = $header->getValue();
                    }
                    if ($header->getName() == 'From') {
                        $from = $header->getValue();
                    }
                }

                // Step 4: Extract message body (if available)
                $body = '';
                if ($payload->getBody()->getSize() > 0) {
                    $body = $payload->getBody()->getData();
                } else {
                    // For multipart messages, extract the body from the 'parts'
                    foreach ($payload->getParts() as $part) {
                        if ($part['mimeType'] === 'text/plain') {
                            $body = $part['body']->getData();
                            break;
                        }
                    }
                }

                // Step 5: Add the message details to the array
                $messageDetails[] = [
                    'id' => $messageId,
                    'threadId' => $messageDetail->getThreadId(),
                    'subject' => $subject,
                    'date' => $date,
                    'from' => $from,
                    'body' => base64_decode(strtr($body, '-_', '+/')) // Decode the body content
                ];
            }

            // Return the detailed messages
            return $messageDetails;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


}
