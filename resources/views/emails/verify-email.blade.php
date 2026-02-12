<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">

    {{-- 🌙 Dark Mode Meta Tags --}}
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">

    <title>Verify Email - ExamBabu</title>

    <style>
        /* -------------------------------------
            GLOBAL RESETS
        ------------------------------------- */
        body {
            margin: 0;
            padding: 0;
            width: 100% !important;
            height: 100% !important;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f7;
            color: #51545e;
        }

        /* -------------------------------------
            DEFAULT (LIGHT MODE) STYLES
        ------------------------------------- */
        .email-wrapper { width: 100%; background-color: #f4f4f7; padding: 0; margin: 0; }
        .email-content { width: 100%; max-width: 600px; margin: 0 auto; }

        .email-body { width: 100%; margin: 0; padding: 0; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .email-body_inner { padding: 35px; }

        .email-masthead { padding: 25px 0; text-align: center; }
        .email-masthead_name { font-size: 24px; font-weight: bold; color: #0777be; text-decoration: none; }

        h1 { margin-top: 0; color: #333333; font-size: 22px; font-weight: bold; text-align: left; }
        p { margin-top: 0; color: #51545e; font-size: 16px; line-height: 1.625em; margin-bottom: 20px; }

        .button {
            display: inline-block;
            background-color: #0777be; /* Brand Blue */
            color: #ffffff !important;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            padding: 12px 30px;
            text-align: center;
        }

        .email-footer { width: 100%; max-width: 570px; margin: 0 auto; padding: 20px; text-align: center; }
        .email-footer p { color: #a8aaaf; font-size: 12px; text-align: center; }

        .sub-copy { font-size: 12px; color: #999999; line-height: 1.5; word-break: break-all; }
        .divider { border-top: 1px solid #edeff2; margin-top: 25px; padding-top: 25px; }

        /* -------------------------------------
            🌙 DARK MODE STYLES
            (Outlook, Apple Mail, Gmail iOS support)
        ------------------------------------- */
        @media (prefers-color-scheme: dark) {
            /* Backgrounds */
            body, .email-wrapper { background-color: #1a1a1a !important; }
            .email-body { background-color: #2d2d2d !important; border: 1px solid #444444 !important; box-shadow: none !important; }

            /* Text Colors */
            h1 { color: #ffffff !important; }
            p, span { color: #dddddd !important; }
            .email-masthead_name { color: #3b82f6 !important; text-shadow: none !important; }

            /* Footer */
            .email-footer p { color: #888888 !important; }
            .sub-copy { color: #888888 !important; }
            .divider { border-color: #444444 !important; }

            /* Button (Keep bright for visibility) */
            .button { background-color: #0777be !important; color: #ffffff !important; }
        }

        /* -------------------------------------
            RESPONSIVE
        ------------------------------------- */
        @media only screen and (max-width: 600px) {
            .email-body_inner { padding: 20px !important; }
            .button { width: 100% !important; display: block; }
        }
    </style>
</head>
<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-content" width="100%" cellpadding="0" cellspacing="0" role="presentation">

                    <tr>
                        <td class="email-masthead">
                            <a href="{{ url('/') }}" class="email-masthead_name">
                                ExamBabu
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td class="email-body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="email-body_inner" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell">
                                        <h1>Hello, {{ $user->first_name }}! 👋</h1>

                                        <p>Thanks for joining <strong>Exam Babu</strong>. We're excited to have you on board!</p>
                                        <p>Please confirm your email address to get full access to your student dashboard, live tests, and results.</p>

                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" role="presentation">
                                            <tr>
                                                <td align="center" style="padding: 20px 0;">
                                                    <a href="{{ $url }}" class="button" target="_blank">Verify My Account</a>
                                                </td>
                                            </tr>
                                        </table>

                                        <p>If you didn't create an account, you can safely ignore this email.</p>

                                        <p>Best regards,<br><strong>The Exam Babu Team</strong></p>

                                        <div class="divider">
                                            <p class="sub-copy">
                                                If the button above doesn't work, copy and paste this link into your browser:
                                                <br>
                                                <a href="{{ $url }}" style="color: #0777be;">{{ $url }}</a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <table class="email-footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td align="center">
                                        <p>
                                            &copy; {{ date('Y') }} Exam Babu. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
