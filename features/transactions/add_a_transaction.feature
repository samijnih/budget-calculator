Feature: Add a transaction to a user
    Background:
        Given a fresh database
        And all the migrations are played
        Given there are users in my system with:
            | id                                   | email        | password | balance_amount | balance_currency | created_at             |
            | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | test@test.fr | test     | 100            | EUR              | 2019-07-27 02:00:00+02 |

    Scenario: I can add a transaction and retrieve it later
        Given a new transaction with:
            | id                                   | user_id                              | label   | amount | currency | type  | date       | created_at             |
            | e416e99b-1ac5-4af0-914f-aa56ffe0ee99 | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | Spotify | 999    | EUR      | debit | 2020-07-27 | 2019-07-27 02:00:00+02 |
        When I call the entity repository to add the transaction
        Then I get a read model from the read model repository with:
            | id                                   | user_id                              | label   | amount | currency | type  | date       | created_at             | updated_at |
            | e416e99b-1ac5-4af0-914f-aa56ffe0ee99 | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | Spotify | 999    | EUR      | debit | 2020-07-27 | 2019-07-27 02:00:00+02 |            |
