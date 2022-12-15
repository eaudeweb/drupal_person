<?php

namespace Drupal\drupal_person\Controller;

use Drupal\backup_migrate\Core\Translation\TranslatableTrait;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\node\Entity\Node;
use Drupal\drupal_person\PersonManager;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for person submission contact.
 */
class ContactPersonController extends ControllerBase implements ContainerInjectionInterface {

  use TranslatableTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The person manager.
   *
   * @var \Drupal\drupal_person\PersonManager
   */
  protected $personManager;

  /**
   * Constructor for Contact Person controller.
   */
  public function __construct(PersonManager $personManager) {
    $this->entityTypeManager = $this->entityTypeManager();
    $this->personManager = $personManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('person.manager'),
    );
  }

  /**
   * Form used to contact the person.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node type.
   *
   * @return array
   *   An array representing a form contact.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function contactForm(Node $node) {
    // Do not continue if the person does not have an email.
    if (!$this->personManager->hasEmail()) {
      throw new NotFoundHttpException();
    }
    $webform = $this->entityTypeManager->getStorage('webform')->load('contact_person');
    if (!$webform instanceof Webform) {
      throw new WebformException('The webform is missing');
    }

    return $this->entityTypeManager->getViewBuilder('webform')->view($webform);
  }

}
