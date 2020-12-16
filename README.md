# inIT SEO Storm
## Note
The plugin is a fork of the Arcane.SEO plugin. Since then it has changed a lot. See the differences below.

> **The plugin is still under development. Keep in mind that installing this version may cause unintended side effects. Once ready, it will be published in the marketplace**

## Installation

The current version of the plugin is under development. Once ready it will be pushed to the OctoberCMS's marketplace.

To install it you have to use git method:

```bash
$ cd /path/to/project-root/
$ git clone git@github.com:initbiz/oc-seo-storm-plugin.git plugins/initbiz/seostorm
$ php artisan october:up
```

[//]: # (Documentation)

## Meta tags (SEO component)
Embed the seo component in your layout or page in the `head` section. The component will render all the meta tags defined in the page.

### Dynamic meta tags
SEO meta tag fields support twig syntax, this makes them more flexible when you have a website with many records and you need to use their attribute values for search results, or generate the title and description of the page from a model field.

![seo fields screenshot](https://i.ibb.co/7JJvNgr/download.png)

##  Automating the `sitemap.xml` generation
To automatically generate the sitemap.xml, follow the steps below:

1. Make sure you have the sitemap.xml enabled in the settings page.

    ![enable sitemap.xml in settings page screenshot](https://i.ibb.co/bgX91G0/e2008635-0938-4cb8-83c8-33180a7144f4.jpg)

2. Go to the editor page of your CMS, static or blog post page, and on the "SEO" tab check the `Enable in the sitemap.xml` checkbox.

    ![sitemap checkbox screenshot](https://i.ibb.co/vVDyPjZ/download.jpg)

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

## The differences between Arcane.SEO and inIT SEO Storm

1. Dropped schema components support,
1. Dropped minify JS and CSS features as they are built in October core,
