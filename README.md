## Note
The plugin is a fork of [Arcane.SEO plugin](https://github.com/mrjeanp/Arcane-SEO). Since then it has changed a lot.

> **The plugin is still under development. Once ready, it will be published in the marketplace**

## Installation

This is a fork of the plugin "Initbiz SEO" and is not yet been published in the October CMS market, to install it do:

```bash
$ cd /path/to/project-root/
$ git clone git@github.com:initbiz/oc-seo-storm-plugin.git plugins/initbiz/seostorm
$ php artisan october:up
$ composer update
```

# How to use
## Meta tags
To enable meta tags, place the SEO component in one or more of your layouts. Make sure meta tags are enabled from the settings page.

### Dynamic meta tags
SEO meta tag fields support twig syntax, this makes them more flexible when you have a website with many records and you need to use their attribute values for search results, or generate the title and description of the page from a model field.

![seo fields screenshot](https://i.ibb.co/7JJvNgr/download.png)

### `RainLab.Translate` support
The twig syntax is very helpful, it allows you to do something like this:

    {{ "This is the meta title of my page" | _ }}

After rendering the page the first time, the string will be registered by the `RainLab.Translate`'s `_` filter as a translation message. This means that these meta tags can become multilingual.

### Restrictions
- Sitemap.xml fields don't use dynamic fields.
- If you are using only one `{{ }}` inside a the property field of a schema.org component, you need to add at least one trailing space, from the code of the page not the October interface. Example:

```
[schemaVideo]
description = "{{ episode.serie.summary }} " <-- this is the trailing space
```


##  Automating the sitemap.xml
To automatically generate the sitemap.xml, follow the steps below:

1. Make sure you have the sitemap.xml enabled in the settings page.

    ![enable sitemap.xml in settings page screenshot](https://i.ibb.co/bgX91G0/e2008635-0938-4cb8-83c8-33180a7144f4.jpg)

2. Go to the editor page of your CMS, static or blog post page, and on the "SEO" tab check the "Enable in the sitemap.xml" checkbox.

    ![sitemap checkbox screenshot](https://i.ibb.co/vVDyPjZ/download.jpg)

3. Visit: http(s)://yourdomain.tld/sitemap.xml

**Note**: The fields in the "Sitemap" section are not dynamic.

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

## Adding structured data (schema.org)

You can write your own schemas on your cms pages, blog posts and static pages. Just locate the Schema tab and follow this syntax:

```yaml
# top level schema
Article:
    # these are properties
   headline: "BIG NEWS OF 2019"
   # property names must be written exactly as schema.org
   datePublished: "09/08/1992"
   # You can specify the type of subschema like this
   publisher@Organization:
    # properties are dynamic
    name: "{{ model.publisher.name }}"
   # you can specify arrays of a type
   author@Person[]:
    0:
        name: "James"
    1:
        name: "Tom"

 # another top level schema
 Organization:
    ...
```

The plugin also comes with components that define some schema.org objects (Article, Product and VideoObject). These components are also available as snippets for `RainLab.Pages` and their properties support twig syntax.

It's highly recommended that you read the [Google guidelines](https://developers.google.com/search/docs/guides/intro-structured-data) if you're not familiar with structured data.

To use these components, all you need to do is drag the ones you need from the inspector to the page editor. **Do not** place them inside the page code as they are rendered by the `seo` component.

![structured data component screenshot](https://i.ibb.co/0CpC5JM/Untitled.png)

**Important**: component field values enclosed in  `{{ }}` are automatically interpreted by October as external properties (https://octobercms.com/docs/cms/components#external-property-values). If you have only one brace pair, then the output will be an empty string if October can't find the external property. As a workaround, You must add a trailing space like this:

![trailing space at the end of the value](https://i.snag.gy/T2Qkzq.jpg)

However, if using multiple braces you won't need to add any space.

**Note**: The components will be removed in a later version.


## Open Graph & Twitter cards
The configuration is done via the Social Media tab. If you don't know about these tags read [the guide for Open Graph from Facebook](https://developers.facebook.com/docs/sharing/webmasters) and [the guide for Twitter cards from Twitter](https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards.html).

**Note:** Twitter cards are automatically set from the OG * fields.

![open graph tab screenshot](https://i.ibb.co/C1wPvhv/download.png)

Currently supported tags are:
- `og:title`defaults to _page meta\_title | page title_
- `og:description` defaults to _page meta\_description | site description_ in the Settings page
- `og:image` defaults to _page image|site image_ in Settings page - Social media tab
- `og:type` defaults to "website"
- `og:site_name` set in the settings page.
- `twitter:title` from `og:title`
- `twitter:description` from `og:description`
- `twitter:image` from `og:image`

**Note:** read the guidelines from Facebook and Twitter linked above for recommended values on these tags.
