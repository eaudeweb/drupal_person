<?php

namespace Drupal\drupal_person\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\drupal_person\PersonManager;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides 'Contact Person' block.
 *
 * @Block(
 *   id = "contact_person",
 *   admin_label = @Translation("Contact Person"),
 *   category = @Translation("EDW")
 * )
 */
class ContactPersonBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The person manager.
   *
   * @var \Drupal\drupal_person\PersonManager
   */
  protected $personManager;

  /**
   * The current node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $person = NULL;

  /**
   * The ContactPersonBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\drupal_person\PersonManager $personManager
   *   The current route match.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, PersonManager $personManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->personManager = $personManager;
    $this->person = $personManager->getPerson();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('person.manager'),
    );
  }

  /**
   * Build a Contact block for a person.
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  public function build() {
    if (!$this->person) {
      return [];
    }
    $build = [
      '#type' => 'container',
      '#attributes' => ['class' => ['person', 'contact']],
    ];
    if ($this->personManager->hasEmail()) {
      $build['email'] = [
        '#type' => 'link',
        '#title' => $this->t('reach out'),
        '#url' => Url::fromRoute('entity.node.person_contact', [
          'node' => $this->person->id(),
        ]),
        '#attributes' => [
          'class' => ['btn', 'btn-primary', 'btn-email'],
          'target' => '_blank',
        ],
        '#weight' => 1,
      ];
    }
    if ($this->personManager->hasTwitter()) {
      $build['twitter'] = [
        '#type' => 'link',
        '#title' => $this->t('twitter'),
        '#url' => Url::fromUri($this->personManager->getTwitterUri()),
        '#attributes' => [
          'class' => ['btn', 'btn-primary', 'btn-twitter'],
          'target' => '_blank',
        ],
        '#weight' => 2,
      ];
    }
    if ($this->personManager->hasLinkedIn()) {
      $build['linkedin'] = [
        '#type' => 'link',
        '#title' => $this->t('LinkedIn'),
        '#url' => Url::fromUri($this->personManager->getLinkedInUri()),
        '#attributes' => [
          'class' => ['btn', 'btn-primary', 'btn-linkedin'],
          'target' => '_blank',
        ],
        '#weight' => 3,
      ];
    }
    if ($this->personManager->hasWebsite()) {
      $build['website'] = [
        '#type' => 'link',
        '#title' => $this->t('website'),
        '#url' => Url::fromUri($this->personManager->getWebsite()),
        '#attributes' => [
          'class' => ['btn', 'btn-primary', 'btn-website'],
          'target' => '_blank',
        ],
        '#weight' => 4,
      ];
    }
    return $build;
  }

  /**
   * Check if exists at least one contact info.
   *
   * @return bool
   *   Return TRUE or FALSE.
   */
  protected function hasContactInfo() {
    return ($this->personManager->hasEmail()
      || $this->personManager->hasTwitter()
      || $this->personManager->hasLinkedIn()
      || $this->personManager->hasWebsite()
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIf(
      $this->person instanceof Node
      && $this->person->bundle() == 'person'
      && $this->hasContactInfo()
    );
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['url']);
  }

}
