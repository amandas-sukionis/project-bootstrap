Feature: Gallery create/edit/delete album
    In order to manipulate albums
    As an admin
    I need to be able create, edit and delete albums

    Scenario: Creating new album
      Given I log in as admin
      And I go to "/admin/admin-gallery/add-album"
      When I fill in "name" with "test album name"
      And I fill in "location" with "test album location"
      And I fill in "shortDescription" with "test album short description"
      And I fill in "fullDescription" with "test album full full full full full full full full description"
      And I press "addAlbumFormSubmit"
      Then I should be on "/admin/admin-gallery"
      And I should see "test album name"
      And the response status code should be 200

    Scenario: Edit created album
      Given I log in as admin
      And I go to "/admin/admin-gallery/edit-album/test-album-name"
      When I fill in "name" with "test album name changed"
      And I press "addAlbumFormSubmit"
      Then I should be on "/admin/admin-gallery"
      And I should see "test album name changed"
      And the response status code should be 200

    Scenario: Delete created album
      Given I log in as admin
      And I go to "/admin/admin-gallery/delete-album/test-album-name"
      Then I should be on "/admin/admin-gallery"
      And I should not see "test album name changed"
      And the response status code should be 200

    Scenario: Fail new album form validation
      Given I log in as admin
      And I go to "/admin/admin-gallery/add-album"
      When I fill in "location" with "test album location"
      And I fill in "shortDescription" with "test album short description"
      And I fill in "fullDescription" with "test album full full full full full full full full description"
      And I press "addAlbumFormSubmit"
      Then I should be on "/admin/admin-gallery/add-album"
      And the response status code should be 200

