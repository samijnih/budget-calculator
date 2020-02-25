# Budget Calculator

I would like to check how much money I can spend after receiving my pay.

I would like to define budgets in order to receive some bad ass recommendations about how to manage my money according to what I want.

## Release changelog

### v0.1

Features:

- authentication system for personal information
- you can register yourself given an email/password with an initial balance
- you can remove your account
- you can add some known transactions like:
    - debit
        - transportation (e.g. subway, train, plane...)
        - subscriptions (e.g. netflix, spotify, amazon prime...)
    - credit
        - refund (e.g. half transportation if you are eligible to that)
- you can display all your current transactions
- you can cleanly quit the process

Note:

You need to register your account before trying to do anything else.

My app aims to be for private use only, so I need to secure the write actions. I don't want people to access transactions of other people ;)

## Next release schedule

### v0.2

- edit transactions
- delete transactions
- define budgets
- display budgets

### v0.3

- rethink the launcher
    - what about getting two options (sign-in / sign-up)
        - selecting *sign-up* could run the user registration command
        - selecting *sign-in* could run a new menu with only authenticated operations
