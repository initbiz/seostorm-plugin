![Arcane SEO banner](https://i.ibb.co/8jCZj2N/arcane-seo-banner.png)

## Installation
You can install from the market place by searching for "arcane seo" or:
```bash
$ cd /path/to/project-root/
$ git clone git@github.com:mstrrain/Arcane-SEO.git plugins/arcane/seo
$ php artisan plugin:refresh Arcane.Seo
$ composer update
```
# How to use
## Meta tags
To enable meta tags, place the SEO component in one or more  of your layouts. Make sure meta tags are enabled from the settings page.

### Dynamic meta tags
SEO meta tag fields support twig syntax, this makes them more flexible when you  have a website with many records and you need to use their attribute values for search results, or generate the title and description of the page from a model field.

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



### Custom models
If you have a custom model that you want to generate the links from, add the full class name of your model in the "Settings" tab of the **CMS page**. If the page has the `blogPost` component, you don't need to set the Model class.

![model class field screenshot](https://i.ibb.co/8g3SrS0/download.jpg)

**Important:** The URL parameters of the page, for example: `/post/:slug`, will be replaced by the attribute values of the model with the same name, so you must ensure the model has an attribute called `slug` in this case.



## Adding structured data (schema.org)
The plugin comes with components that define some schema.org objects (Article, Product and VideoObject). These components are also available as snippets for `RainLab.Pages` and their properties support twig syntax. 

It's highly recommended that you read the [Google guidelines](https://developers.google.com/search/docs/guides/intro-structured-data) if you're not familiar with structured data.

To use these components, all you need to do is drag the ones you need from the inspector to the page editor. **Do not** place them inside the page code as they are rendered by the `seo` component.

![structured data component screenshot](https://i.ibb.co/0CpC5JM/Untitled.png)

**Important**: component field values enclosed in  `{{ }}` are automatically interpreted by October as external properties (https://octobercms.com/docs/cms/components#external-property-values). If you have only one brace pair, then the output will be an empty string if October can't find the external property. As a workaround, You must add a trailing space like this:

![trailing space at the end of the value](https://i.snag.gy/T2Qkzq.jpg)

However, if using multiple braces you won't need to add any space.

## Open Graph & Twitter cards
The configuration is done via the Open Graph tab. If you don't know about these tags read [the guide for Open Graph from Facebook](https://developers.facebook.com/docs/sharing/webmasters) and [the guide for Twitter cards from Twitter](https://developer.twitter.com/en/docs/tweets/optimize-with-cards/overview/abouts-cards.html).

**Note:** Twitter cards are automatically set from the OG * fields.

![open graph tab screenshot](https://i.ibb.co/C1wPvhv/download.png)

Currently supported tags are:
- `og:title`defaults to _page meta\_title | page title_
- `og:description` defaults to _page meta\_description | site description_ in the Settings page
- `og:image` defaults to  _page image|site image_ in Settings page - Open Graph tab
- `og:type` defaults to "website"
- `og:site_name` set in the settings page.
- `twitter:title` from `og:title`
- `twitter:description` from `og:description`
- `twitter:image` from `og:image`

**Note:** read the guidelines from Facebook and Twitter linked above for recommended values on these tags.

