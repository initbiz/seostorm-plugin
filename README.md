SEO Storm
===

[//]: # (Introduction)
Here's where serious SEO in OctoberCMS is starting.

Originally forked from `Arcane.SEO` plugin but since then it has changed a lot.

## Set of features

* automatically generate titles and other meta tags on the pages,
* manage custom meta tags from the backend,
* manage robots tags in an easy way,
* set favicon from mediafinder,
* manage Open Graph parameters,
* edit `.htaccess` without leaving backend,
* partially migrate from `Arcane.SEO` with a single click of a button,
* generate `sitemap.xml` automatically with parameters in URLs,
* support `RainLab.Pages`,
* easily extend custom models to display SEO parameters in the backend,
* use Twig parameters to fill meta tags,
* some smaller features you will probably like :)

[//]: # (Documentation)

## Using SEO Storm

Install the plugin and drop `SEO` component in your `<head>` section of the page, whether it's page or layout.

Go to `Settings` -> `SEO Storm` -> `General settings` and set whatever settings you'd like to have.

## Common use-cases

### Global prefix/suffix in the page's title

1. Go to `Settings` -> `SEO Storm` -> `General settings` and set `Enable title and description meta tags` to `on`.
1. Fill the `Site name` and `Site name separator` fields.
1. Select if you want to have the `Site name` added to the beginning or to the end (prefix or suffix).

![Global prefix/suffix in the page's title](docs/common-global-prefix-suffix-title.png)

### Automatically set title of the page basing on a model (like blog post)

The following instructions will work for any other field that is accessible from the page. The only thing you have to know is under what variable is the title you would like to set it by. I this example we'll use `Question` model which is used on [our page here](https://init.biz/faqs/what-is-octobercms).

Go to `Editor` -> `Pages` -> Select the page -> click `SEO Storm` button

![Automatically set meta attribute basing on model values](docs/common-auto-meta-parameter.png)

The same approach will work for most of the other parameters. See `Dynamic meta tags` section for more information.

### Generating `sitemap.xml`

1. Go to `Settings` -> `SEO Storm` -> `General settings` and set `Enable sitemap.xml` to `on`.

## Dynamic meta tags

In many situations we want to have meta attributes set dynamically basing on the variables on the page. In most typical case it may be a blog post which may be available using `{{ post }}` variable. Using `Dynamic meta tags` we may set the attributes basing on such variables.

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

Keep in mind that you can fill those fields with basically everything that is accepted by Twig. This includes conditionals in case of empty values. Let's say you have some model that has two fields: `name` and `meta_title`. You want to set title using the `meta_title` field but if it's not present, use `name` instead. You may easily build the logic like this:

```twig
    {{ model.meta_title ?: model.name }}
```

## Advanced sitemap
To automatically generate the sitemap.xml, follow the steps below:

1. Make sure you have the sitemap.xml enabled in the settings page.
2. Go to the editor page of your CMS, static or to a model that has been registered as Storomed Models, and on the "SEO" tab check the `Enable in the sitemap.xml` checkbox.
3. Visit: http(s)://yourdomain.tld/sitemap.xml

### Dynamic URLs based on the models
If you have a model that you want to generate the links from in the sitemap you can use those three parameters to accomplish that:

1. Model class
2. Model scope
3. Model params

To make it work you will have to enter both:

1. the class (e.g. `Author\Plugin\Models\ModelClass`) to the `model_class` field, and
1. model parameters that match the parameters in the URL (e.g. `slug:slug`).

If you want to filter the objects of the model, use the `model_scope` (more about scopes [here](https://octobercms.com/docs/database/model#query-scopes)).
For example `isPublished`.

#### Model params
First parameter of the definition is the URL parameter while the second one is the corresponding model attribute.

> For example: `post:slug` means we have a `post` parameter in the URL and `slug` attribute in the model.

If you want to add more attributes, split them by pipe character (`|`). For example: `date:date|slug:slug`.

Model parameters adds a nice feature to pull the parameter from the related objects.

For example:

    slug:slug|category:categories.slug

> Note: The relation attribute will always take the first element of the relation.

## Open Graph & Twitter cards
The configuration is done via the `Open Graph` tab.

> If you don't know what these are take a look at [the guide for Open Graph from Facebook](https://developers.facebook.com/docs/sharing/webmasters) and [the guide for Twitter cards from Twitter](https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards.html).

> **Note:** Twitter cards are automatically set from the OG fields.

![open graph tab screenshot](https://i.ibb.co/C1wPvhv/download.png)

Currently supported tags are:
- `og:title` defaults to page's `meta_title` or `title`,
- `og:description` defaults to page's `meta_description`, `viewBag['description']`, `site_description` from the `Settings`,
- `og:image` defaults to `site_image` from the `Settings`,
- `og:type` defaults to `website`,
- `twitter:title` got from `og:title`,
- `twitter:description` got from `og:description`,
- `twitter:image` got from `og:image`.

**Note:** read the guidelines from Facebook and Twitter linked above for recommended values on these tags.

## Registering SEO Stormed Models
The most awesome feature in the SEO Storm is dynamic extending models to have the `seo_options` attributes.

To make it work you have to register your models as `stormed`.
Add the `registerStormedModels()` method in your `Plugin.php` file, for example:

    public function registerStormedModels()
    {
        return [
            '\Author\Plugin\Models\ExampleModel' => [
                'prefix' => 'viewBag',
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

This will make the models automatically extended and form widgets to automatically have the required fields.

SEO Storm automatically takes care of `CMS page` and `Static pages` models.

`seo_options` are stored in the automatically binded polymorphic relation beetween the model and `SeoOptions` model.
This feature frees you from defining the attributes in your models, tables and `fields.yaml`.

To make it more clear:

* `placement` defines where the fields are going to be rendered. Possible options are: `fields`, `tabs` and `secondaryTabs`,
* `prefix` defines the relation prefix to automatically add to the fields definition, by default `seo_options` - you probably won't want to change it,
* `excludeFields` will exclude the fields from the form

`excludeFields` can also define the inverse by `*`, so in the second example we will have all the fields excluded except those defined later.
