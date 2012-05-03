BeamMeUp
========

This is but one plugin of two in active developement. This git repo is not where the active working code resides, but I will push here as major bits of progrss are made. See below for a project synopsis.

Pop-up Radio
------------

Omeka is an open-source framework designed for organizations such as libraries, museums, and radio producers to archive their digital content. Written in PHP, it uses the MySQL relational database management system and the Apache webserver. Because it does not require root access to install, one can use Omeka on server space provided by popular vendors such as Dreamhost. The considerable expertise needed for deployment on a server with root access, such as those provided by Amazon Web Services’ Elastic Cloud Computing, is not required.

Omeka is the Wordpress of web-based archival software. Its tools may not be state-of-the-art, but it fulfills a critical need in lowering the bar for deployment. The hottest Silicon Valley start-ups have trouble finding capable systems engineers to manage their servers. Not-for-profits do not have the resources to attract such talent. They need Omeka.

In addition to its ease of deployment, Omeka has several other advantages. It has a paid staff out of George Mason University that develop and maintain its core. Like any good open source project, it also has a large and active community of volunteer developers. Omeka provides the architecture to enable such developers (including the Pop-up Radio Archive) to expand Omeka’s functionality through plugins.

The issues with Omeka are two-fold. From an archival standpoint, the server that hosts Omeka and its data is typically rented space in the cloud. A change in credit card number can delete this data. From an outreach standpoint, making the front-end to an Omeka-powered website is difficult, and these sites are often lightly trafficked.

The Pop-up Radio Project addresses these issues by getting the content in an Omeka database off of Omeka. For posterity, this data is sent to the Internet Archive, whose job is to store digital media in perpetuity. For accessibility, the data is sent to SoundCloud, an audio repository with a superb interface and skyrocketing popularity.

To do so, two plugins have been developed: one for the Internet Archive and the other for SoundCloud. Omeka is a middle man. It already has the code necessary to get data from a local machine (e.g. a radio producer’s laptop) to its database. The Pop-up Radio plugins provide the cURL code to get the data from its database to more accessible, better maintained third parties.

Technical Components
--------------------
Passing the data to these third parties is easier said than done. Below is a synopsis of the technical components that Pop-up Radio has used to do so.

cURL
Both plugins are, at their core, cURL scripts. cURL is an open-source command-line utility that runs HTTP calls that, in this case, is implemented through a PHP object. The Internet Archive plugin sends HTTP PUT requests to an API that mimics the popular S3 service of Amazon Web Services. The Internet Archive’s S3-like API must create a bucket after the first file is received before subsequent files are sent. This requires that a metadata object goes out like a scout, and then creates the bucket, while a while loop runs, after which other files are sent in a multithreaded process. This currently presents UI problems, as an Omeka user may stare at a white screen for upwards of two minutes before the process completes. This issue will be addressed in the future by getting the on a background thread.

The SoundCloud plugin has no such problems with bucket creation, and its scripts can simply be sent in a multithreaded process. This process should still be daemonized for the sake of better user interaction.
Authentication

The Internet Archive uses public-key authentication. On installation, the Pop-up Radio plugin prompts the user to enter the Internet Archive-provided public key, and then saves this key in Omeka’s persistent memory. This public key is then passed to the Internet Archive in the headers of the HTTP PUT. 

In the case of SoundCloud, OAuth authentication is used. Users are prompted to log into SoundCloud on installation, and then soundcloud sends a token back to the plugin. This token is saved in Omeka’s persistent memory and sent in the header of an HTTP POST request to authenticate tracks that are posted to SoundCloud.

Omeka Admin Integration
All of the above processes are exclusively run in Omeka’s after_save_item hook, which is implemented after an Omeka item is saved. An item’s admin screen has a tab for each plugin, and this tab enables users to check boxes that specify whether they would like the plugin to work for the item in question.

This presents a convenient way to get new items to the third parties, but means that old items have to be resaved in order for the plugins to work for them. There is also no way for an Omeka user to see which files have gotten to the Internet Archive and SoundCloud without going to these sites. In the future, Omeka’s database should keeps track of what uploads have occur. Through Omeka’s Model-View-Controller framework, this data can be clearly displayed on one table. Futhermore, the cURL script, both for individual and groups of non-uploaded files, can be run from this same page.