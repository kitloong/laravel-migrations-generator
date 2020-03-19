Feature: Migrations With Schema

  Scenario: Creating a Table
    When I generate a migration with name 'create_orders_table' and fields 'name:string'
    Then I should see "Created:"
    And the generated migration should match my 'CreateOrdersTable' stub

  Scenario: Creating a Table With Complex Fields
    When I generate a migration with name 'create_orders_table' and fields 'title:string(50):unique, body:text:unique:nullable'
    Then I should see "Created:"
    And the generated migration should match my 'CreateComplexOrdersTable' stub

  Scenario: Dropping a Table
    When I generate a migration with name 'drop_orders_table' and fields 'title:string'
    Then I should see "Created:"
    And the generated migration should match my 'DropOrdersTable' stub

  Scenario: Adding to a Table
    When I generate a migration with name 'add_title_to_orders_table' and fields 'title:string'
    Then I should see "Created:"
    And the generated migration should match my 'AddTitleToOrdersTable' stub

  Scenario: Removing from a Table
    When I generate a migration with name 'remove_title_from_orders_table' and fields 'title:string'
    Then I should see "Created:"
    And the generated migration should match my 'RemoveTitleFromOrdersTable' stub
