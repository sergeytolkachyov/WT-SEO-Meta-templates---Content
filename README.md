# WT SEO Meta templates - Content
This plugin adds variables with data from Joomla content categories and articles and their custom fields, which can be processed by the main WT SEO Meta templates plugin. The plugin parameters allow you to set a single template for all categories and / or articles, according to which the text of the <title> tag and the text of the meta tag description will be formed. If the plugin parameters are disabled completely, the it will simply generate variables that you can use when filling in the <title> and meta-description manually.
For this plugin to work, you need to [install the main plugin WT SEO Meta templates](https://web-tolk.ru/en/dev/joomla-plugins/wt-seo-meta-templates.html)
# SEO Variables for Joomla articles (com_content)
## SEO Variables from Joomla content article for SEO template
- {CC_ARTICLE_ID} - Article Id
- {CC_ARTICLE_TITLE} - Article title
- {CC_ARTICLE_HITS} - Article hits
- {CC_ARTICLE_CATEGORY_TITLE} - Article category title
- {CC_ARTICLE_INTRO} - Article intro text trimmed to the specified number of characters
- {CC_ARTICLE_AUTHOR} - Article author
## Article custom fields
- {CC_ARTICLE_FIELD_XXX_TITLE} - Field title, where "XXX" - is field id. For examlpe, {CC_ARTICLE_FIELD_14_TITLE}
- {CC_ARTICLE_FIELD_XXX_VALUE} - Field value, where "XXX" - is field id. For examlpe, {CC_ARTICLE_FIELD_14_VALUE}
- {CC_ARTICLE_FIELD_XXX} - Field's both title and value separated by space, where "XXX" - is field id. For examlpe, {CC_ARTICLE_FIELD_14}. If field title is "Color", and value is \"red\", then this short-code outputs "Color red".
## SEO Variables from Joomla content category for template
- {CC_CATEGORY_TITLE} - Category title
- {CC_CATEGORY_ID} - Category id
- {CC_PARENT_CATEGORY_TITLE} - Parent category title
## Category custom fields
- {CC_CATEGORY_FIELD_XXX_TITLE} - Field title, where "XXX" - is field id. For examlpe, {CC_CATEGORY_FIELD_14_TITLE}
- {CC_CATEGORY_FIELD_XXX_VALUE} - Field value, where "XXX" - is field id. For examlpe, {CC_CATEGORY_FIELD_14_VALUE}
- {CC_CATEGORY_FIELD_XXX} - Field's both title and value separated by space, where "XXX" - is field id. For examlpe, {CC_CATEGORY_FIELD_14}. If field title is "Color", and value is "red", then this short-code outputs "Color red".
## Pagination
The plugin unifies pagination pages with suffixes of the form " - page #N". The suffix text is located in the plugin settings
https://web-tolk.ru/en/dev/joomla-plugins/wt-seo-meta-templates-content.html
