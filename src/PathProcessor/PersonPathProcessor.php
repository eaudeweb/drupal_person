<?php

namespace Drupal\drupal_person\PathProcessor;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Processes the inbound/outbound path using path alias lookups.
 *
 * Inspired by the subpathauto module, this is used to make Person routes
 * that rely on canonical URLs work with URL aliases.
 *
 * E.g. we defined a route for the /node/{node}/contact path. This path will
 * not work on /node-alias/contact by default. This path processor will strip
 * components from the end of the URL until the remaining path matches an URL
 * alias.
 *
 * When an URL alias is found, the canonical URL for that alias is taken and
 * combined with the stripped URL components. If this resulting URL matches a
 * route, we go to that page.
 *
 * Example:
 *   - if /node/1/contact matches a route, go to that page.
 */
class PersonPathProcessor implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * An alias manager for looking up the system path.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path processor.
   *
   * @var \Drupal\Core\PathProcessor\InboundPathProcessorInterface
   */
  protected $pathProcessor;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Builds PathProcessor object.
   *
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $pathProcessor
   *   The path processor.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   An alias manager for looking up the system path.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match manager.
   */
  public function __construct(InboundPathProcessorInterface $pathProcessor, AliasManagerInterface $aliasManager, EntityTypeManagerInterface $entityTypeManager, RouteMatchInterface $routeMatch) {
    $this->pathProcessor = $pathProcessor;
    $this->aliasManager = $aliasManager;
    $this->entityTypeManager = $entityTypeManager;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {
    // Rewrite /person/name/contact to /node/nid/contact.
    if (preg_match('!^\/people\/(\S*)\/(contact)!', $path, $matches)) {
      $pathArray = explode('/', ltrim($path, '/'));
      array_splice($pathArray, 2);
      $alias = '/' . implode('/', $pathArray);
      $path = $this->aliasManager->getPathByAlias($alias);
      $path = $path . '/contact';
    }

    return $path;
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CamelCaseParameterName)
   * @SuppressWarnings(PHPMD.CamelCaseVariableName)
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    // Rewrite /node/nid/contact to /person/name/contact.
    if (preg_match('!^\/node\/(\d*)\/(contact)!', $path, $matches)) {
      $nid = $matches[1];
      $node = $this->entityTypeManager->getStorage('node')->load($nid);
      $alias = $this->aliasManager->getAliasByPath('/node/' . $node->id());
      $path = $alias . '/contact';
      if ($bubbleable_metadata) {
        $bubbleable_metadata->addCacheTags($node->getCacheTags());
      }
    }
    return $path;
  }

}
