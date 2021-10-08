SEO Storm - ultimate SEO tool for OctoberCMS!
===

[//]: # (Introduction)

![SEO Storm - ultimate SEO tool for OctoberCMS](https://raw.githubusercontent.com/initbiz/seostorm-plugin/master/docs/seo-storm.png)

Originally forked from the abandoned `Arcane.SEO` plugin we have made numerous improvements and added many new features.

---

- [Key Features](#key-features)
- [Using SEO Storm](#using-seo-storm)
- [Common use-cases](#common-use-cases)
    - [Global prefix/suffix in the page's title](#global-prefixsuffix-in-the-pages-title)
    - [Automatically set the title of the page based on a model (such as a blog post)](#automatically-set-the-title-of-the-page-based-on-a-model-such-as-a-blog-post)
    - [Generating your `sitemap.xml`](#generating-your-sitemapxml)
- [Dynamic meta tags](#dynamic-meta-tags)
    - [Fallback values](#fallback-values)
- [Advanced `sitemap.xml`](#advanced-sitemapxml)
    - [Model params](#model-params)
    - [Model scopes](#model-scopes)
- [Open Graph & Twitter cards](#open-graph-&-twitter-cards)
- [Custom models with SEO parameters](#custom-models-with-seo-parameters)
- [Troubleshooting](#troubleshooting)
    - [Problem: Cards looking bad when pasting a link on social media](#problem-cards-looking-bad-when-pasting-a-link-on-social-media)
- [Future plans/features](#future-plansfeatures)

---

## Key Features

* Automatically generates titles and other meta tags on the page
* Manages custom meta tags from the backend
* Manages the robots meta tag in an easy way
* Sets a favicon from October's Mediafinder
* Manages Open Graph parameters
* Edits `.htaccess` without leaving the backend
* Partially migrates from `Arcane.SEO` with a single click
* Generates a `sitemap.xml` file automatically with parameters in URLs
* Supports `RainLab.Pages`,
* Provides an easy way to extend custom models for SEO parameters in the backend
* Uses Twig parameters to fill meta tags
* Plus numerous smaller features you will love :)

[//]: # (Documentation)

## Using SEO Storm

Install the plugin and then add the `SEO` component in site's `head` section, whether it's a page or layout.

Go to `Settings` -> `SEO Storm` -> `General settings` and configure to suit your needs.

## Common use-cases

### Global prefix/suffix in the page's title

1. Go to `Settings` -> `SEO Storm` -> `General settings` and set `Enable title and description meta tags` to `on`.
1. Fill the `Site name` and `Site name separator` fields.
1. Select if you want to have the `Site name` added to the beginning or to the end (prefix or suffix).

![Global prefix/suffix in the page's title](https://raw.githubusercontent.com/initbiz/seostorm-plugin/master/docs/common-global-prefix-suffix-title.png)

### Automatically set the title of the page based on a model (such as a blog post)

The following instructions will work for any other field that is accessible from the page. The only thing you have to decide is the variable title you would like to set it by. In this example we'll use the `Question` model which is featured on [our page here](https://init.biz/faqs/what-is-octobercms).

Go to `Editor` -> `Pages` -> Select the page -> and click the `SEO Storm` button. Complete the field using Twig syntax as shown in the screenshot below:

![Automatically set the meta attribute based on model values](https://raw.githubusercontent.com/initbiz/seostorm-plugin/master/docs/common-auto-meta-parameter.png)

The same approach will work for most of the other parameters. See the `Dynamic meta tags` section for more information.

### Generating your `sitemap.xml`

Go to `Settings` -> `SEO Storm` -> `General settings` and set `Enable sitemap.xml` to `On`.

That's basically everything you need. **Just make sure that all the pages you want to be included in the `sitemap.xml` have the `Enable in sitemap.xml` option checked**

![Enable in sitemap.xml checkbox](https://raw.githubusercontent.com/initbiz/seostorm-plugin/master/docs/enable-in-sitemap.png)

If you want to handle more advanced customizations, see the `Advanced sitemap.xml` section.

## Dynamic meta tags

In many situations you'll want to have the meta attributes set dynamically based on the variables on the page. A typical example would be a blog post which uses the `{{ post }}` variable. Using `Dynamic meta tags` we can set the attributes based on such variables.

Tags that are currently using Twig syntax:
* `meta title`
* `meta description`
* `canonical URL`
* `advanced robots`
* `OG type`
* `OG title`
* `OG description`
* `OG image`
* `OG video`
* `Twitter card`
* `Twitter title`
* `Twitter description`
* `Twitter image`

### Fallback values

Keep in mind that you can basically fill those fields with anything that is accepted by Twig. This includes conditionals in the case of empty values. For example let's say you have a model that has two fields: `name` and `meta_title`. You want to set the title using the `meta_title` field but if it's not present, you want SEO Storm to use `name` instead. You can build the logic like this:

```twig
    {{ model.meta_title ?: model.name }}
```

## Advanced `sitemap.xml`

You may want to fill parameters in you URLs based on the models in the page (e.g. a blog post's slug). To achieve that, you can set the following parameters in your page's settings:

1. Model class
1. Model params
1. Model scope

In the following example we have the model `Question`, but you may easily use `Post` or any other value that this page is displaying.

![Advanced sitemap.xml configuration](https://raw.githubusercontent.com/initbiz/seostorm-plugin/master/docs/advanced-sitemap-xml-params.png)

Take a closer look at those two parameters:

1. the class (e.g. `Author\Plugin\Models\ModelClass`) to the `model_class` field, and
1. model parameters that match the parameters in the URL (e.g. `slug:slug`).

The first one will say SEO Storm, which model it should use for this page to generate URLs in the sitemap. The second one is pairing between the URL parameter and model attribute (which match which).

### Model params
As described above, the first parameter of the definition is the URL parameter while the second one is the corresponding model attribute.

> For example: `post:slug` means we have a `post` parameter in the URL and `slug` attribute in the model.

If you want to add more attributes, split them by pipe character (`|`). For example: `date:date|slug:slug`.

You may want to create a URL such as `/blog/:category/:postslug`. To achieve this we use the dot syntax to fetch the attribute from the related object, as this example demonstrates:

    postslug:slug|category:categories.slug

This method will work for all relation types but if it's a  "one to many" relationship, remember that only the first one will be used.

### Model scopes

Sometimes you may want to filter the records listed in the `sitemap.xml`. To do this define a scope in your model and provide its name in the third parameter. It will then be used by SEO Storm to filter the records. More about scopes [here](https://octobercms.com/docs/database/model#query-scopes).

For Posts generated by the `RainLab.Blog` you can use `isPublished` to fetch the published ones only. Otherwise, all of the posts will be listed in the `sitemap.xml`.

## Open Graph & Twitter cards

You can set Open Graph and Twitter cards attributes using SEO Storm, as well. Keep in mind, that both are filled using `OG` fields. (SEO Storm doesn't support using different content for each).

If you want to learn more about OG and Twitter cards take a look at [the guide for Open Graph from Facebook](https://developers.facebook.com/docs/sharing/webmasters) and [the guide for Twitter cards from Twitter](https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards.html).

![Open Graph and Twitter attributes](https://raw.githubusercontent.com/initbiz/seostorm-plugin/master/docs/open-graph-twitter-attributes.png)

Currently supported tags are:
- `og:title` defaults to page's `meta_title` or `title`,
- `og:description` defaults to page's `meta_description`, or `site_description` from the `Settings`,
- `og:image` defaults to `site_image` from the `Settings`,
- `og:type` defaults to `website`,
- `twitter:title` got from `og:title`,
- `twitter:description` got from `og:description`,
- `twitter:image` got from `og:image`.

**Note:** Please read the guidelines from Facebook and Twitter linked above for recommended values on these tags. Take a look at the `Dynamic meta tags` section to see which of those support the Twig syntax.

## Custom models with SEO parameters

SEO Storm lets you easily define the models to which you'd like to have SEO parameters dynamically attached.

> You don't have to make any other customizations - SEO Storm takes care of extending the models and storing the attributes in the DB.
We call such models `Stormed`. To register a model as `Stormed` implement a `registerStormedModels` method in your plugin's registration file (`Plugin.php`).

Add the `registerStormedModels()` method in your `Plugin.php` file, for example:

```php
    public function registerStormedModels()
    {
        return [
            '\Author\Plugin\Models\ExampleModel' => [
                'placement' => 'tabs',
            ],
        ];
    }
```

Using this definition SEO Storm will take care of extending the model and form widgets in backend controllers. The above example will add SEO fields to the `ExampleModel` as shown in the following example (the example uses our `Question` model):

![Example stormed model registration](https://raw.githubusercontent.com/initbiz/seostorm-plugin/master/docs/example-stormed-model.png)

If you wish to customize the fields displayed in the backend you can use the `excludeFields` attribute in the registration method. You may also use inverted syntax, so that all the fields are removed except the ones listed. See the example below:

```php
    public function registerStormedModels()
    {
        return [
            '\Author\Plugin\Models\ExampleModel' => [
                'placement' => 'tabs',
                'excludeFields' => [
                    'model_class',
                    'model_scope',
                    'model_params',
                ],
            ],
            '\Author\Plugin\Models\ExampleModel2' => [
                'placement' => 'secondaryTabs',
                'excludeFields' => [
                    '*',
                    'meta_title',
                    'meta_description',
                    'og_image',
                    'og_ref_image',
                    'og_title',
                    'og_description',
                ],
            ],
        ];
    }
```

The following parameters are supported in the `registerStormedModels` method:

* `placement` defines where the fields are going to be rendered. It's either: `fields`, `tabs` and `secondaryTabs`,
* `prefix` defines the relation prefix to automatically add to the fields definition, by default it's `seo_options` (you have to know what you're doing before changing it, so please be careful)
* `excludeFields` will exclude the fields from the form as described above

Note: By default, SEO Storm takes care of `CMS pages` and `Static pages` so you don't have to define them yourself.

## Troubleshooting

### Problem: Cards looking bad when pasting a link on social media

Reason: Open Graph is not enabled or it's configured improperly. See [the guide for Open Graph from Facebook](https://developers.facebook.com/docs/sharing/webmasters) and [the guide for Twitter cards from Twitter](https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards.html) to get better understanding on the parameters.

## Future plans/features

1. Order `sitemap.xml` urls using models' priorities,
1. Take all SEO attributes of the models into consideration while generating `sitemap.xml`
