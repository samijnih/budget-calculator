Feature: Modify an existing user
    Background:
        Given a fresh database
        And all the migrations are played
        Given there are users in my system with:
            | id                                   | email        | password | balance_amount | balance_currency | created_at             |
            | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | test@test.fr | test     | 100            | EUR              | 2019-07-27 02:00:00+02 |

    Scenario: I can replace the previous balance of a user with a new one
        Given I retrieve a user identified by id cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb
        And I replace the balance with "200" "USD" at 2019-07-27 03:00:00+02
        When I call the entity repository to update the user
        Then I get a read model from the read model repository with:
            | id                                   | email        | password | balance_amount | balance_currency | created_at             | updated_at             |
            | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | test@test.fr | test     | 200            | USD              | 2019-07-27 02:00:00+02 | 2019-07-27 03:00:00+02 |
