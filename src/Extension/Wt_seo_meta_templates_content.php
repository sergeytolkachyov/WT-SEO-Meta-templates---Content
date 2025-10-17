<?php
/**
 * @package     WT SEO Meta templates
 * @subpackage  WT SEO Meta templates - Content
 * @version     2.0.2
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2022 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since       1.0.0
 */

namespace Joomla\Plugin\System\Wt_seo_meta_templates_content\Extension;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;

// No direct access
\defined('_JEXEC') or die;

final class Wt_seo_meta_templates_content extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var bool $autoloadLanguage
	 *
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * @var bool $show_debug Show debug flag
	 * @since 1.0.0
	 */
	protected bool $show_debug = false;


	/**
	 * @inheritDoc
	 *
	 * @return string[]
	 *
	 * @throws \Exception
	 * @since 4.1.0
	 *
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onWt_seo_meta_templatesAddVariables' => 'onWt_seo_meta_templatesAddVariables'
		];
	}

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		if (PluginHelper::isEnabled('system', 'wt_seo_meta_templates'))
		{
			$main_plugin        = PluginHelper::getPlugin('system', 'wt_seo_meta_templates');
			$main_plugin_params = new Registry($main_plugin->params);
			$this->show_debug   = $main_plugin_params->get('show_debug');
		}
	}

	public function onWt_seo_meta_templatesAddVariables($event): void
	{
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: start');
		$app    = $this->getApplication();
		$option = $app->getInput()->get('option');
		$id     = $app->getInput()->get('id');

		if ($option == 'com_content')
		{

			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: After load Field helper');
			$variables = [];
			//Массив для тайтлов и дескрипшнов по формуле для передачи в основной плагин
			$seo_meta_template = [];
			// Short codes for com_content category view
			if ($app->getInput()->get('view') == 'category')
			{

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: Before load Content category');
				/**
				 * @var \Joomla\Component\Content\Site\Model\CategoryModel $model `ignore_request = false` - we get a current model instance with populateState(). If `ignore_request = true` - populateState() will be ignored
				 */
				$model = $app->bootComponent('com_content')
					->getMVCFactory()
					->createModel('Category', 'Site', ['ignore_request' => false]);
				// Trick due to bug in core populateState() method
				// @see https://github.com/joomla/joomla-cms/issues/46311
				$model->getState('category.id');
				$category           = $model->getCategory();
				$category->jcfields = FieldsHelper::getFields("com_content.categories", $category, true);


				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: After load Content category');

				/*
				 * Com_content category variables for short codes
				 */
				//Com_content category name
				$variables[] = [
					'variable' => 'CC_CATEGORY_TITLE',
					'value'    => $category->title,
				];
				//Com_content category id
				$variables[] = [
					'variable' => 'CC_CATEGORY_ID',
					'value'    => $category->id,
				];

				//Com_content parent category title
				$parent_category = $category->get('_parent');
				$variables[]     = [
					'variable' => 'CC_PARENT_CATEGORY_TITLE',
					'value'    => $parent_category->title,
				];

				foreach ($category->jcfields as $field)
				{
					// com_content article custom field title
					$variables[] = [
						'variable' => 'CC_CATEGORY_FIELD_' . $field->id . '_TITLE',
						'value'    => $field->title,
					];

					// com_content article custom field value
					$variables[] = [
						'variable' => 'CC_CATEGORY_FIELD_' . $field->id . '_VALUE',
						'value'    => $field->value,
					];

					// com_content article custom field title and value merged
					$variables[] = [
						'variable' => 'CC_CATEGORY_FIELD_' . $field->id,
						'value'    => $field->title . ' ' . $field->value,
					];

				}

				/**
				 * Если включена глобальная перезапись <title> категории. Все по формуле.
				 */
				if ($this->show_debug)
				{
					$this->prepareDebugInfo('', '<p><strong>Com_content area</strong>: category</p>');
					$this->prepareDebugInfo('', '<p><strong>Com_content Title</strong>: ' . $category->title . '</p>');
					$this->prepareDebugInfo('', '<p><strong>Com_content Meta desc:</strong> ' . $category->metadesc . '</p>');
				}

				$category_title_category_exclude = $this->params->get('cc_category_title_category_exclude');
				if (!is_array($category_title_category_exclude))
				{
					$category_title_category_exclude = [];
				}

				if ($this->params->get('global_cc_category_title_replace') == 1 && !in_array($category->id, $category_title_category_exclude))
				{

					// Переписываем все title категорий глобально
					// У категорий нет отдельного поля для title
					if ($this->show_debug)
					{
						$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_CATEGORY_TITLE_REPLACE') . '</p>');
					}
					$title_template = $this->params->get('content_category_title_template');
					if (!empty($title_template))
					{
						$seo_meta_template['title'] = $title_template;
					}
				}

				/*
				 * Если включена глобальная перезапись description категории. Все по формуле.
				 */

				$category_metadesc_category_exclude = $this->params->get('cc_category_metadesc_category_exclude');
				if (!is_array($category_metadesc_category_exclude))
				{
					$category_metadesc_category_exclude = [];
				}
				if ($this->params->get('global_cc_category_description_replace') == 1 && !in_array($category->id, $category_metadesc_category_exclude))
				{

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $category->metadesc
					 */

					if ($this->params->get('global_cc_category_description_replace_only_empty') == 1)
					{
						if ($this->show_debug)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE_ONLY_EMPTY') . '</p>');
						}

						if (empty($category->metadesc))
						{
							if ($this->show_debug)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_EMPTY_META_DESCRIPTION_FOUND') . '</p>');
							}
							$description_template = $this->params->get('content_category_meta_description_template');

							if (!empty($description_template))
							{
								$seo_meta_template['description'] = $description_template;
							}

						}
					}
					else
					{
						//Переписываем все meta description категорий глобально
						if ($this->show_debug)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE') . '</p>');
						}
						$description_template = $this->params->get('content_category_meta_description_template');
						if (!empty($description_template))
						{
							$seo_meta_template['description'] = $description_template;
						}
					}
				}


				/*
				* Добавляем или нет суффикс к title и meta-description страницы
				* для страниц пагинации.
				*/

				//$limitstart - признак страницы пагинации, текущая страница пагинации
				$limitstart = $app->getInput()->get('limitstart', 0, 'uint');
				if (isset($limitstart) && (int) $limitstart > 0 && $this->params->get('enable_page_title_and_metadesc_pagination_suffix') == 1)
				{

					$model->getItems();

					//текущая страница пагинации
					$pagination                  = $model->getPagination();
					$current_pagination_page_num = $pagination->pagesCurrent;

					$title    = $app->getDocument()->getHeadData()['title'];
					$metadesc = $app->getDocument()->getHeadData()['description'];

					// Тексты суффиксов из параметров плагина
					if (!empty($this->params->get('page_title_pagination_suffix_text')))
					{
						// Суффиксы для страниц пагинации - "- страница NNN".
						$pagination_suffix_title = sprintf(Text::_($this->params->get('page_title_pagination_suffix_text')), $current_pagination_page_num);

						//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
						$seo_meta_template['title'] = $title . ' ' . $pagination_suffix_title;
					}

					if (!empty($this->params->get('page_metadesc_pagination_suffix_text')))
					{

						$pagination_suffix_metadesc = sprintf(Text::_($this->params->get('page_metadesc_pagination_suffix_text')), $current_pagination_page_num);

						//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
						$seo_meta_template['description'] = $metadesc . ' ' . $pagination_suffix_metadesc;
					}
				}//pagination

			} // Short codes for com_content articles view
			elseif ($app->getInput()->get('view') == 'article')
			{

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: Before load article');
				$this->prepareDebugInfo('', '<p><strong>Com_content area</strong> article</p>');

				$model             = $app->bootComponent('com_content')
					->getMVCFactory()
					->createModel('Article', 'Site', ['ignore_request' => false]);

				$article           = $model->getItem($id);
				$article->jcfields = FieldsHelper::getFields("com_content.article", $article, true);

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: After load article');
				$this->prepareDebugInfo('', '<p><strong>Com_content Title</strong>: ' . $article->title . '</p>');
				$this->prepareDebugInfo('', '<p><strong>Com_content Meta desc:</strong> ' . $article->metadesc . '</p>');
				/*
				 * Com_content article variables for short codes
				 */
				//Article id
				$variables[] = [
					'variable' => 'CC_ARTICLE_ID',
					'value'    => $article->id,
				];
				//Article title
				$variables[] = [
					'variable' => 'CC_ARTICLE_TITLE',
					'value'    => $article->title,
				];
				//Article hits
				$variables[] = [
					'variable' => 'CC_ARTICLE_HITS',
					'value'    => $article->hits,
				];

				//Article's parent category title
				$variables[] = [
					'variable' => 'CC_ARTICLE_CATEGORY_TITLE',
					'value'    => $article->category_title,
				];

				//Article author
				$variables[] = [
					'variable' => 'CC_ARTICLE_AUTHOR',
					'value'    => $article->author,
				];

				//Article intro text
				if (!empty($article->introtext))
				{
					(int) $intro_text_max_lenght = $this->params->get('cc_article_intro_text_max_chars', 200);

					$article_intro_text = HTMLHelper::_('content.prepare', $article->introtext, '', 'com_content.article');
					$article_intro_text = trim(strip_tags(html_entity_decode($article_intro_text, ENT_QUOTES, 'UTF-8')));
					$article_intro_text = str_replace(["\r\n", "\r", "\n", "\t", '  ', '   '], ' ', $article_intro_text);

					if ($intro_text_max_lenght > 3)
					{
						$intro_text_max_lenght = $intro_text_max_lenght - 3; // For '...' in the end of string
					}
					$article_intro_text = mb_substr($article_intro_text, 0, $intro_text_max_lenght, 'utf-8');
					$article_intro_text = $article_intro_text . '...';

				}
				else
				{
					$article_intro_text = '';
				}


				$variables[] = [
					'variable' => 'CC_ARTICLE_INTRO',
					'value'    => $article_intro_text,
				];

				foreach ($article->jcfields as $field)
				{
					// com_content article custom field title
					$variables[] = [
						'variable' => 'CC_ARTICLE_FIELD_' . $field->id . '_TITLE',
						'value'    => $field->title,
					];

					// com_content article custom field value
					$variables[] = [
						'variable' => 'CC_ARTICLE_FIELD_' . $field->id . '_VALUE',
						'value'    => $field->value,
					];

					// com_content article custom field title and value merged
					$variables[] = [
						'variable' => 'CC_ARTICLE_FIELD_' . $field->id,
						'value'    => $field->title . ' ' . $field->value,
					];

				}

				$article_title_category_exclude = $this->params->get('cc_article_title_category_exclude');
				if (!is_array($article_title_category_exclude))
				{
					$article_title_category_exclude = [];
				}
				$article_metadesc_category_exclude = $this->params->get('cc_article_metadesc_category_exclude');
				if (!is_array($article_metadesc_category_exclude))
				{
					$article_metadesc_category_exclude = [];
				}

				/**
				 * Специфичные сео-формулы для материалов конкретной категории
				 */
				$custom_templates_for_articles_in_specified_category = [];
				foreach ($this->params->get('custom_templates_for_articles_in_specified_category') as $custom_template)
				{
					$custom_templates_for_articles_in_specified_category[$custom_template->category]['title']    = $custom_template->title;
					$custom_templates_for_articles_in_specified_category[$custom_template->category]['metadesc'] = $custom_template->metadesc;
				}

				if ($this->params->get('global_article_title_replace') == 1 && !in_array($article->catid, $article_title_category_exclude))
				{

					/**
					 * Если переписываем только пустые. Там, где пустое
					 * $article->params->get('article_page_title')
					 */

					if ($this->params->get('global_article_title_replace_only_empty') == 1)
					{
						if ($this->show_debug)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_TITLE_REPLACE_ONLY_EMPTY') . '</p>');
						}

						if (empty($article->params->get('article_page_title')) == true)
						{
							if ($this->show_debug)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_EMPTY_ARTICLE_TITLE_FOUND') . '</p>');
							}


							if (isset($custom_templates_for_articles_in_specified_category[$article->catid]))
							{
								// Специфичная сео-формула для материалов данной категории
								$title_template = $custom_templates_for_articles_in_specified_category[$article->catid]['title'];
								if ($this->show_debug)
								{
									$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_CUSTOM_TEMPLATE_FOR_ARTICLES_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
								}
							}
							else
							{
								// Глобальная сео-формула для всех материалов
								$title_template = $this->params->get('content_article_title_template');
							}

							if (!empty($title_template))
							{
								$seo_meta_template['title'] = $title_template;
							}
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->show_debug)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_TITLE_REPLACE') . '</p>');
						}

						if (isset($custom_templates_for_articles_in_specified_category[$article->catid]))
						{
							// Специфичная сео-формула для материалов данной категории
							$title_template = $custom_templates_for_articles_in_specified_category[$article->catid]['title'];

							if ($this->show_debug)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_CUSTOM_TEMPLATE_FOR_ARTICLES_IN_SPECIFIED_CATEGORY_FOUND') . ' - title</p>');
							}

						}
						else
						{
							// Глобальная сео-формула для всех материалов
							$title_template = $this->params->get('content_article_title_template');
						}
						if (!empty($title_template))
						{
							$seo_meta_template['title'] = $title_template;
						}
					}

				}

				/**
				 * Если включена глобальная перезапись description материала. Все по формуле.
				 */

				if ($this->params->get('global_article_meta_description_replace') == 1 && !in_array($article->catid, $article_metadesc_category_exclude))
				{

					/**
					 * Если переписываем только пустые. Там, где пустое
					 * $article->description
					 */

					if ($this->params->get('global_article_meta_description_replace_only_empty') == 1)
					{
						if ($this->show_debug)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_META_DESCRIPTION_REPLACE_ONLY_EMPTY') . '</p>');
						}
						if (empty($article->metadesc) == true)
						{
							if ($this->show_debug)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_EMPTY_ARTICLE_META_DESCRIPTION_FOUND') . '</p>');
							}

							if (isset($custom_templates_for_articles_in_specified_category[$article->catid]))
							{
								// Специфичная сео-формула для материалов данной категории
								$description_template = $custom_templates_for_articles_in_specified_category[$article->catid]['metadesc'];
								if ($this->show_debug)
								{
									$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_CUSTOM_TEMPLATE_FOR_ARTICLES_IN_SPECIFIED_CATEGORY_FOUND') . ' - meta description</p>');
								}
							}
							else
							{
								// Глобальная сео-формула для всех материалов
								$description_template = $this->params->get('content_article_meta_description_template');

							}


							if (!empty($description_template))
							{
								$seo_meta_template['description'] = $description_template;
							}
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->show_debug)
						{
							$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_TITLE_REPLACE') . '</p>');

						}
						if (isset($custom_templates_for_articles_in_specified_category[$article->catid]))
						{
							// Специфичная сео-формула для материалов данной категории
							$description_template = $custom_templates_for_articles_in_specified_category[$article->catid]['metadesc'];
							if ($this->show_debug)
							{
								$this->prepareDebugInfo('', '<p>' . Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_CUSTOM_TEMPLATE_FOR_ARTICLES_IN_SPECIFIED_CATEGORY_FOUND') . ' - meta description</p>');
							}
						}
						else
						{
							// Глобальная сео-формула для всех материалов
							$description_template = $this->params->get('content_article_meta_description_template');

						}

						if (!empty($description_template))
						{
							$seo_meta_template['description'] = $description_template;
						}
					}
				}
			}

			/**
			 * Include files with custom SEO variables and overrides from
			 * plugins/system/wt_seo_meta_templates_content/customvariables
			 */
			if (\is_dir(__DIR__ . '/customvariables'))
			{
				$custom_variables = Folder::files(__DIR__ . '/customvariables');
				if ($this->show_debug)
				{
					$this->prepareDebugInfo('Custom variables folder found', __DIR__ . '/customvariables');
					$this->prepareDebugInfo('Custom variables files found (' . count($custom_variables) . ')', $custom_variables);
				}
				foreach ($custom_variables as $custom_variable)
				{
					require_once(__DIR__ . '/customvariables/' . $custom_variable);
				}

			}

			$data = [
				'variables'          => $variables,
				'seo_tags_templates' => $seo_meta_template,
			];

			$this->prepareDebugInfo('SEO variables', $data);

			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: Before return data. End.');

			$event->setArgument('result', $data);
		}
	}

	/**
	 * Prepare html output for debug info from main function
	 *
	 * @param string $debug_section_header
	 * @param string|array $debug_data
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.3.0
	 */
	private function prepareDebugInfo(string $debug_section_header, $debug_data): void
	{
		if ($this->show_debug)
		{
			$session      = $this->getApplication()->getSession();
			$debug_output = $session->get("wtseometatemplatesdebugoutput");
			if (!empty($debug_section_header))
			{
				$debug_output .= "<details style='border:1px solid #0FA2E6; margin-bottom:5px;'>";
				$debug_output .= "<summary style='background-color:#384148; color:#fff; padding:10px;'>" . $debug_section_header . "</summary>";
			}

			if (is_array($debug_data))
			{
				$debug_data   = print_r($debug_data, true);
				$debug_output .= "<pre style='background-color: #eee; padding:10px;'>";
			}

			$debug_output .= $debug_data;
			if (is_array($debug_data))
			{
				$debug_output .= "</pre>";
			}
			if (!empty($debug_section_header))
			{
				$debug_output .= "</details>";
			}
			$session->set("wtseometatemplatesdebugoutput", $debug_output);
		}
	}
}