<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Overdue library book</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: Arial, Helvetica, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f4f6f8; padding: 24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width: 560px; background-color: #ffffff; border-radius: 10px; overflow: hidden; border: 1px solid #e5e7eb;">
                    <tr>
                        <td style="background-color: #b02a37; padding: 24px 28px; color: #ffffff;">
                            <div style="font-size: 13px; letter-spacing: 0.08em; text-transform: uppercase; opacity: 0.85;">{{ $libraryName }}</div>
                            <div style="font-size: 22px; font-weight: bold; margin-top: 4px;">Overdue book notice</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin: 0 0 16px; font-size: 15px;">Hello {{ $borrowing->user->name }},</p>
                            <p style="margin: 0 0 20px; font-size: 15px; line-height: 1.6;">
                                Our records show that the book below is overdue by
                                <strong>{{ $daysOverdue }} day{{ $daysOverdue === 1 ? '' : 's' }}</strong>.
                                Please return it as soon as possible.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8f9fa; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 16px 18px;">
                                        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Book</div>
                                        <div style="font-size: 16px; font-weight: bold; margin: 4px 0 10px;">{{ $borrowing->book->title }}</div>
                                        <div style="font-size: 13px; color: #4b5563;">Author: {{ $borrowing->book->author?->name ?? 'Unknown' }}</div>
                                        <div style="font-size: 13px; color: #4b5563;">Due date: <strong>{{ $borrowing->due_at->format('F j, Y') }}</strong></div>
                                        <div style="font-size: 13px; color: #4b5563;">Loan reference: #{{ $borrowing->id }}</div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #4b5563;">
                                Return the book at the library desk. If the book has been lost or damaged, please inform the librarian so the record can be updated.
                            </p>
                            <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #4b5563;">
                                Thank you,<br>
                                <strong>{{ $libraryName }}</strong>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 16px 28px; background-color: #f8f9fa; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                            This is an automated notice. Please contact the library for questions about your account.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
