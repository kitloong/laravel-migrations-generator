Feature: Generators

  Scenario Outline: Generation
    When I generate a <command> with "<argument>"
    Then I should see "Created:"
    And "<generatedFilePath>" should match my stub

    Examples:
      | command    | argument         | generatedFilePath                                               |
      | model      | Order            | workbench/way/generators/tests/tmp/Order.php                    |
      | seed       | recent_orders    | workbench/way/generators/tests/tmp/RecentOrdersTableSeeder.php  |
      | controller | OrdersController | workbench/way/generators/tests/tmp/OrdersController.php         |
      | view       | orders.bar.index | workbench/way/generators/tests/tmp/orders/bar/index.blade.php   |
