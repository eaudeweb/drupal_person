<?php

namespace Drupal\drupal_person;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\Entity\Node;

/**
 * Contains helpful functions used for managing people.
 */
class PersonManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $person = NULL;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  const TWITTER_VALIDATOR = '/^(http(s)?:\/\/)(?:www\.)?twitter\.com\/([a-zA-Z0-9_]+)/';
  const LINKEDIN_VALIDATOR = '/^(http(s)?:\/\/)?([\w]+\.)?linkedin\.com\/(pub|in|profile)/';

  /**
   * Constructor for PersonManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match manager.
   */
  public function __construct(EntityTypeManager $entityTypeManager, RouteMatchInterface $routeMatch) {
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $routeMatch;
    $this->person = $this->routeMatch->getParameter('node') ?? NULL;
  }

  /**
   * Return the person entity.
   *
   * @return \Drupal\node\Entity\Node|null
   *   Return a node, null otherwise.
   */
  public function getPerson() {
    return $this->person;
  }

  /**
   * Get email for a person.
   *
   * @return string|null
   *   Return an email if exists, FALSE otherwise.
   */
  public function getEmail() {
    if ($this->hasEmail()) {
      return $this->person->get('field_email')->value;
    }
    return NULL;
  }

  /**
   * Get name for a person.
   *
   * @return string
   *   Return a name.
   */
  public function getName() {
    return $this->person->getTitle();
  }

  /**
   * Check if the person has an email.
   *
   * @return bool
   *   TRUE if a person has email, FALSE otherwise.
   */
  public function hasEmail() {
    return ($this->person instanceof Node
      && $this->person->hasField('field_email')
      && !$this->person->get('field_email')->isEmpty()
    );
  }

  /**
   * Get Twitter uri for a person.
   *
   * @return string|null
   *   Return a Twitter Url if exists, FALSE otherwise.
   */
  public function getTwitterUri() {
    if ($this->hasTwitter()) {
      return $this->person->get('field_twitter_profile')->uri;
    }
    return NULL;
  }

  /**
   * Validate Twitter & LinkedIn Uri.
   *
   * @param string $uri
   *   A string containing a URI.
   * @param string $validator
   *   A string containing a regex used for validation.
   *
   * @return bool
   *   Return TRUE if string is valid, FALSE otherwise.
   */
  public function validateUri(string $uri, string $validator) {
    return preg_match($validator, $uri) === 1;
  }

  /**
   * Check if the person has a Twitter account.
   *
   * @return bool
   *   TRUE if a person has Twitter, FALSE otherwise.
   */
  public function hasTwitter() {
    return ($this->person->hasField('field_twitter_profile')
      && !$this->person->get('field_twitter_profile')->isEmpty()
    );
  }

  /**
   * Get LinkedIn uri for a person.
   *
   * @return string|null
   *   Return a LinkedIn Uri if exists, FALSE otherwise.
   */
  public function getLinkedInUri() {
    if ($this->hasLinkedIn()) {
      return $this->person->get('field_linkedin_profile')->uri;
    }
    return NULL;
  }

  /**
   * Check if the person has a LinkedIn account.
   *
   * @return bool
   *   TRUE if a person has LinkedIn, FALSE otherwise.
   */
  public function hasLinkedIn() {
    return ($this->person->hasField('field_linkedin_profile')
      && !$this->person->get('field_linkedin_profile')->isEmpty()
    );
  }

  /**
   * Get website link for a person.
   *
   * @return string|null
   *   Return a website Uri if exists, FALSE otherwise.
   */
  public function getWebsite() {
    if ($this->hasWebsite()) {
      return $this->person->get('field_website')->uri;
    }
    return NULL;
  }

  /**
   * Check if the person has a website.
   *
   * @return bool
   *   TRUE if a person has website, FALSE otherwise.
   */
  public function hasWebsite() {
    return ($this->person->hasField('field_website')
      && !$this->person->get('field_website')->isEmpty()
    );
  }

}
