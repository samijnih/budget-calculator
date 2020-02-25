Feature: Remove an existing user
    Background:
        Given a fresh database
        And all the migrations are played
        Given there are users in my system with:
            | id                                   | email        | password | balance_amount | balance_currency | created_at             |
            | cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb | test@test.fr | test     | 100            | EUR              | 2019-07-27 02:00:00+02 |

    Scenario: I can remove a user
        Given I retrieve a user identified by id cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb
        When I call the entity repository to remove the user
        Then I get null from the read model repository with id cf3d48cd-38e1-4f53-a8e0-9fe7e48fbabb
