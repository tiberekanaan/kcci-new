<?php

namespace Drupal\lb_ux\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\layout_builder\Element\LayoutBuilder;
use Drupal\layout_builder\SectionStorageInterface;

/**
 * Alters the Layout Builder UI element.
 */
class LayoutBuilderUX extends LayoutBuilder {

  /**
   * {@inheritdoc}
   */
  protected function buildAdministrativeSection(SectionStorageInterface $section_storage, $delta) {
    $build = parent::buildAdministrativeSection($section_storage, $delta);

    $section_label = $build['#attributes']['aria-label'];

    $build['configure']['#title'] = $this->t('<span class="visually-hidden">Configure @section</span>', ['@section' => $section_label]);
    $build['configure']['#url'] = Url::fromRoute('layout_builder.configure_section_form', $build['configure']['#url']->getRouteParameters());

    $build['remove']['#title'] = $this->t('<span class="visually-hidden">Remove @section</span>', ['@section' => $section_label]);

    $build['actions'] = [
      '#type' => 'container',
      '#weight' => -100,
      '#attributes' => [
        'class' => [
          'layout-builder__actions',
          'layout-builder__actions__section',
        ],
      ],
      'label' => [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#attributes' => [
          'class' => ['layout-builder__section-label'],
        ],
        'content' => ['#markup' => $section_label],
      ],
      'configure' => $build['configure'],
      'remove' => $build['remove'],
    ];
    unset($build['configure'], $build['remove'], $build['section_label']);

    foreach (Element::children($build['layout-builder__section']) as $region) {
      foreach (Element::children($build['layout-builder__section'][$region]) as $uuid) {
        if (in_array($uuid, ['layout_builder_add_block', 'region_label'])) {
          continue 1;
        }

        $preview_fallback_string = $build['layout-builder__section'][$region][$uuid]['#attributes']['data-layout-content-preview-placeholder-label'];
        $route_parameters = $build['layout-builder__section'][$region][$uuid]['#contextual_links']['layout_builder_block']['route_parameters'];

        // Remove default contextual links.
        // unset($build['layout-builder__section'][$region][$uuid]['#contextual_links']['layout_builder_block']);

        // Ensure the 'content' key is present, as set by
        // \Drupal\layout_builder\EventSubscriber\BlockComponentRenderArray.
        assert(isset($build['layout-builder__section'][$region][$uuid]['content']));

        // Replace content with actions and previous content.
        $build['layout-builder__section'][$region][$uuid]['content'] = [
          'actions' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => [
                'layout-builder__actions',
                'layout-builder__actions__block',
              ],
              'tabindex' => 0,
            ],
            'label' => [
              '#type' => 'html_tag',
              '#tag' => 'span',
              '#attributes' => [
                'class' => ['layout-builder__block-label'],
              ],
              'content' => ['#markup' => $preview_fallback_string],
            ],
            'move' => [
              '#type' => 'link',
              '#title' => $this->t('<span class="visually-hidden">Move @block</span>', ['@block' => $preview_fallback_string]),
              '#url' => Url::fromRoute('layout_builder.move_block_form', $route_parameters),
              '#attributes' => [
                'class' => [
                  'use-ajax',
                  'layout-builder__link',
                  'layout-builder__link--move',
                ],
                'data-dialog-type' => 'dialog',
                'data-dialog-renderer' => 'off_canvas',
              ],
            ],
            'configure' => [
              '#type' => 'link',
              '#title' => $this->t('<span class="visually-hidden">Configure @block</span>', ['@block' => $preview_fallback_string]),
              '#url' => Url::fromRoute('layout_builder.update_block', $route_parameters),
              '#attributes' => [
                'class' => [
                  'use-ajax',
                  'layout-builder__link',
                  'layout-builder__link--configure',
                ],
                'data-dialog-type' => 'dialog',
                'data-dialog-renderer' => 'off_canvas',
              ],
            ],
            'remove' => [
              '#type' => 'link',
              '#title' => $this->t('<span class="visually-hidden">Remove @block</span>', ['@block' => $preview_fallback_string]),
              '#url' => Url::fromRoute('layout_builder.remove_block', $route_parameters),
              '#attributes' => [
                'class' => [
                  'use-ajax',
                  'layout-builder__link',
                  'layout-builder__link--remove',
                ],
                'data-dialog-type' => 'dialog',
                'data-dialog-renderer' => 'off_canvas',
              ],
            ],
          ],
          'content' => $build['layout-builder__section'][$region][$uuid]['content'],
        ];
      }
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function layout(SectionStorageInterface $section_storage) {
    $build = parent::layout($section_storage);
    $build['#attached']['library'][] = 'lb_ux/drupal.lb_ux';
    $build['#attached']['library'][] = 'lb_ux/drupal.lb_ux_message';
    return $build;
  }

}
