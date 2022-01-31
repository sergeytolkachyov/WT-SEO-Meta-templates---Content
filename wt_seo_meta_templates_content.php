<?php
/**
 * @package     WT SEO Meta templates
 * @subpackage  WT SEO Meta templates - Content
 * @version     1.3.0
 * @Author      Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2022 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since       1.0
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Profiler\Profiler;

class plgSystemWt_seo_meta_templates_content extends CMSPlugin
{
	protected $autoloadLanguage = true;

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	public function onWt_seo_meta_templatesAddVariables()
	{
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: start');
		$app    = Factory::getApplication();
		$option = $app->input->get('option');
		$id     = $app->input->get('id');

		if ($option == 'com_content')
		{
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_content/models/', 'ContentModel');
			JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: After load Field helper');
			$variables = array();
			//Массив для тайтлов и дескрипшнов по формуле для передачи в основной плагин
			$seo_meta_template = array();
			// Short codes for com_content category view
			if ($app->input->get('view') == 'category')
			{

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: Before load Content category');
				$model              = BaseDatabaseModel::getInstance('Category', 'ContentModel');
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


				/*
				 * Если включена глобальная перезапись <title> категории. Все по формуле.
				 */
				if ($this->params->get('show_debug') == 1)
				{
					$this->prepareDebugInfo('','<p><strong>Com_content area</strong>: category</p>');
					$this->prepareDebugInfo('','<p><strong>Com_content Title</strong>: ' . $category->title . '</p>');
					$this->prepareDebugInfo('','<p><strong>Com_content Meta desc:</strong> ' . $category->metadesc . '</p>');
				}

				$category_title_category_exclude = $this->params->get('cc_category_title_category_exclude');
				if (!is_array($category_title_category_exclude))
				{
					$category_title_category_exclude = array();
				}

				if ($this->params->get('global_cc_category_title_replace') == 1 && !in_array($category->id, $category_title_category_exclude))
				{

					// Переписываем все title категорий глобально
					// У категорий нет отдельного поля для title
					if ($this->params->get('show_debug') == 1)
					{
						$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_CATEGORY_TITLE_REPLACE').'</p>');
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
					$category_metadesc_category_exclude = array();
				}
				if ($this->params->get('global_cc_category_description_replace') == 1 && !in_array($category->id, $category_metadesc_category_exclude))
				{

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $category->metadesc
					 */

					if ($this->params->get('global_cc_category_description_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE_ONLY_EMPTY').'</p>');
						}

						if (empty($category->metadesc) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_EMPTY_META_DESCRIPTION_FOUND').'</p>');
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
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_CATEGORY_META_DESCRIPTION_REPLACE').'</p>');
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
				$limitstart = $app->input->get('limitstart', 0, 'uint');
				if (isset($limitstart) && (int) $limitstart > 0)
				{

					if ($this->params->get('enable_page_title_and_metadesc_pagination_suffix') == 1)
					{
						$articles_model     = BaseDatabaseModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
						// Get the pagination request variables
						$app_params = Factory::getApplication()->getParams();
						$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');

						if (($app->input->get('layout') === 'blog') || $app_params->get('layout_type') === 'blog')
						{
							$limit = $app_params->get('num_leading_articles') + $app_params->get('num_intro_articles') + $app_params->get('num_links');
						}
						else
						{
							$limit = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.limit', 'limit', $app_params->get('display_num'), 'uint');
						}

						$num_links = $app_params->get('num_links');
						$limit = (int) $limit - (int) $num_links;


						$articles_model->setState('params', $app_params);
						$articles_model->setState('filter.category_id', $category->id);
						$articles_model->setState('list.limit', $limit);
						$articles_model->setState('list.start', $limitstart);
						$articles_model->setState('list.links', $app_params->get('num_links'));
						$articles_model->setState('filter.published', 1);
						//теукщая страница пагинации
						$pagination = $articles_model->getPagination();
						$current_pagination_page_num = $pagination->pagesCurrent;

						if (!empty($this->params->get('page_title_pagination_suffix_text')))
						{
							// Тексты суффиксов из параметров плагина
							$pagination_suffix_title = sprintf(Text::_($this->params->get('page_title_pagination_suffix_text')), $current_pagination_page_num);
							// Суффиксы для страниц пагинации - "- страница NNN".
							if (!empty($seo_meta_template['title']) && !empty($pagination_suffix_title))
							{
								$seo_meta_template['title'] = $seo_meta_template['title'] . ' ' . $pagination_suffix_title;
							}
							elseif (!empty($pagination_suffix_title))
							{
								//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
								$seo_meta_template['title'] = $category->title . ' ' . $pagination_suffix_title;
							}

						}

						if (!empty($this->params->get('page_metadesc_pagination_suffix_text')))
						{

							$pagination_suffix_metadesc = sprintf(Text::_($this->params->get('page_metadesc_pagination_suffix_text')), $current_pagination_page_num);

							// Суффиксы для страниц пагинации - "- страница NNN".
							if (!empty($seo_meta_template['description']) && !empty($pagination_suffix_metadesc))
							{
								$seo_meta_template['description'] = $seo_meta_template['description'] . ' ' . $pagination_suffix_metadesc;
							}
							elseif (!empty($pagination_suffix_metadesc))
							{
								//Если шаблоны отключены - просто добавляем суффиксы в пагинацию
								$seo_meta_template['description'] = $category->metadesc . ' ' . $pagination_suffix_metadesc;
							}
						}
					}

				}//pagination

			}
			// Short codes for com_content articles view
			elseif ($app->input->get('view') == 'article')
			{

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: Before load article');
				$this->prepareDebugInfo('','<p><strong>Com_content area</strong> article</p>');
				$model             = BaseDatabaseModel::getInstance('Article', 'ContentModel');
				$article           = $model->getItem($id);
				$article->jcfields = FieldsHelper::getFields("com_content.article", $article, true);

				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: After load article');
				$this->prepareDebugInfo('','<p><strong>Com_content Title</strong>: ' . $article->title . '</p>');
				$this->prepareDebugInfo('','<p><strong>Com_content Meta desc:</strong> ' . $article->metadesc . '</p>');
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

					$article_intro_text = trim(strip_tags(html_entity_decode($article->introtext, ENT_QUOTES, 'UTF-8')));
					$article_intro_text = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '   '), ' ', $article_intro_text);
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
					$article_title_category_exclude = array();
				}
				$article_metadesc_category_exclude = $this->params->get('cc_article_metadesc_category_exclude');
				if (!is_array($article_metadesc_category_exclude))
				{
					$article_metadesc_category_exclude = array();
				}
				if ($this->params->get('global_article_title_replace') == 1 && !in_array($article->catid, $article_title_category_exclude))
				{

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $article->params->get('article_page_title')
					 */

					if ($this->params->get('global_article_title_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_TITLE_REPLACE_ONLY_EMPTY').'</p>');
						}

						if (empty($article->params->get('article_page_title')) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_EMPTY_ARTICLE_TITLE_FOUND').'</p>');
							}
							$title_template = $this->params->get('content_article_title_template');
							if (!empty($title_template))
							{
								$seo_meta_template['title'] = $title_template;
							}
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_TITLE_REPLACE').'</p>');
						}
						$title_template = $this->params->get('content_article_title_template');
						if (!empty($title_template))
						{
							$seo_meta_template['title'] = $title_template;
						}
					}
				}

				/*
				 * Если включена глобальная перезапись description материала. Все по формуле.
				 */

				if ($this->params->get('global_article_meta_description_replace') == 1 && !in_array($article->catid, $article_metadesc_category_exclude))
				{

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $article->description
					 */

					if ($this->params->get('global_article_meta_description_replace_only_empty') == 1)
					{
						if ($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_META_DESCRIPTION_REPLACE_ONLY_EMPTY').'</p>');
						}
						if (empty($article->metadesc) == true)
						{
							if ($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_EMPTY_ARTICLE_META_DESCRIPTION_FOUND').'</p>');
							}
							$description_template = $this->params->get('content_article_meta_description_template');
							if (!empty($description_template))
							{
								$seo_meta_template['description'] = $description_template;
							}
						}
					}
					else
					{
						//Переписываем все глобально
						if ($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_CONTENT_DEBUG_GLOBAL_ARTICLE_TITLE_REPLACE');
						}
						$description_template = $this->params->get('content_article_meta_description_template');
						if (!empty($description_template))
						{
							$seo_meta_template['description'] = $description_template;
						}
					}
				}


			}//elseif ($app->input->get('view') == 'article')


			$data = array(
				'variables'          => $variables,
				'seo_tags_templates' => $seo_meta_template,
			);


			$this->prepareDebugInfo('SEO variables',$data);

			if ($this->params->get('show_debug') == 1)
			{
				$session    = Factory::getSession();
				$debug_info = $session->get("wtseometatemplatesdebugoutput");

				echo "<details style='border:1px solid #0FA2E6; margin-bottom:5px; padding:10px;'>";
				echo "<summary style='background-color:#384148; color:#fff; padding:10px;'>WT SEO Meta templates debug information</summary>";
				echo $debug_info;
				echo '</details>';
				$session->clear("wtseometatemplatesdebugoutput");

			}

			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - com_content provider plugin</strong>: Before return data. End.');

			return $data;
		}//if($option == 'com_content')
		return;
	}

	/**
	 * Prepare html output for debug info from main function
	 * @param $debug_section_header string
	 * @param $debug_data string|array
	 *
	 * @return void
	 * @since 1.3.0
	 */
	private function prepareDebugInfo($debug_section_header, $debug_data):void
	{
		if ($this->params->get('show_debug') == 1)
		{
			$session      = Factory::getSession();
			$debug_output = $session->get("wtseometatemplatesdebugoutput");
			if (!empty($debug_section_header))
			{
				$debug_output .= "<details style='border:1px solid #0FA2E6; margin-bottom:5px;'>";
				$debug_output .= "<summary style='background-color:#384148; color:#fff; padding:10px;'>" . $debug_section_header . "</summary>";
			}

			if (is_array($debug_data) || is_object($debug_data))
			{
				$debug_data   = print_r($debug_data, true);
				$debug_output .= "<pre style='background-color: #eee; padding:10px;'>";
			}

			$debug_output .= $debug_data;
			if (is_array($debug_data) || is_object($debug_data))
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
}//plgSystemWt_seo_meta_templates_content