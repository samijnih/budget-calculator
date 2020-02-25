Feature: Register a new user
    Background:
        Given a fresh database
        And all the migrations are played

    Scenario: I can register a user and retrieve it later
        Given a new user with:
            | id                                   | email        | password | balance_amount | balance_currency | created_at             |
            | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | test@test.fr | test     | 2875           | EUR              | 2019-07-27 02:00:00+02 |
        When I call the entity repository to register the user
        Then I get a read model from the read model repository with:
            | id                                   | email        | password | balance_amount | balance_currency | created_at             | updated_at |
            | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | test@test.fr | test     | 2875           | EUR              | 2019-07-27 02:00:00+02 |            |
