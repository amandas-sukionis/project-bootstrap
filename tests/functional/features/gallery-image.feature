Feature: Gallery  image
  In order to manipulate images
  As an admin
  I need to be able create, edit and delete images

  Scenario: Check add image route
    Given I log in as admin
    And I go to "/gallery/admin/add-album"
    When I fill in "name" with "test album name"
    And I fill in "location" with "test album location"
    And I fill in "shortDescription" with "test album short description"
    And I fill in "fullDescription" with "test album full full full full full full full full description"
    And I press "addAlbumFormSubmit"
    Then I should be on "/gallery/admin"
    And I should see "test album name"

    When I go to "/gallery/admin/upload-images/test-album-name"
    And I press "uploadImageFormSubmit"
    Then I should be on "/gallery/admin/upload-images/test-album-name"
    And the response status code should be 200
    And I go to "/gallery/admin/delete-album/test-album-name"

