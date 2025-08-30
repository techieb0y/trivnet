## Trivnet 2.10 by KD8GBL (Peter Corbett, peter@corbettdigital.net)

This software traces its roots to an ARESdata packet application, from which KB8ZQZ based [his trivnetdb](https://www.kb8zqz.org/trivnetdb/).
The 145.67 / MN Packet Network group adapted this for use at the Twin Cities Marathon.

In 2009, this version using PHP and uses PostgreSQL was started as ground-up rewrite, preserving only the general web UI layout.

Several features have been added over the years, but the core idea remains to be a general-purpose database to track attributes of people with at least one unique ID, and a log of state changes and messages for each.

## Caveats

As the primary deployment environment has been the temporary and isolated usage for the duration of the Twin Cities Marathon and similar events, within additional limitations imposed by FCC Part 97, this code is by no means an example of secure practices.

Initially, I'd tried to maintain compatibility between the web UI and the packet CLI, but that hasn't been maintained; anything packet-related is now vestigial and not accounted for in a Docker deployment.

## Deployment

As of Fall 2023, Trivnet is intended to be deployed as a group of containers using `docker compose`:

* The trivnet web application
* The trivnet async batch job processor
* The PostgreSQL database (the official `postgres` image)
* Anicilary Prometheus and Grafana instances.

This repository contains a GitLab-CI pipeline which builds the image used for the trivnet application itself, and exports it as a tar file suitable for use with `docker load`.

This repo also has pipeline steps which fetch the FCC ham callsign database and prepare it for loading into the PostgreSQL database as part of initial setup.

The included `docker-compose.yaml` deploys the web app to port 8080; you may want to front-end this with e.g. Apache or nginx, particularly if you want to add TLS (when used on non-amateur networks.)

Some settings are found in `config.inc`, in particular the IDs for data types and values that get special handling (particularly default search keys for use in the multi-edit screen; see help.html for details.) This is not currently exposed via a Docker volume; changes require a container image rebuild.

Previusly, this repo's pipeline generated an RPM for deployment on CentOS 7.

## Log into the system

When you browse to the trivnet site, you'll be prompted to log in. Use an FCC-issued callsign, or the word 'guest'.

## Set up the database fields

On the admin page, under the Datatypes tab, you can add fields to the system. Each data type (field name) has a short name and a long name -- the short name should be one word, preferably all lower case; this is used for the packet side of things.  The web interface uses the long name ('label') everywhere. Exact Match is for things like marathon bib numbers; without it, searches will also return a partial/substring match. Pre-loaded are several fields useful for marathons and similar events, but they can be adjusted as needed.

If you have a field like a bib number that's a guarenteed-present, guarenteed-unique value, you can take the ID number and put it in the `$config["multidefault"]` line in `includes/config.inc``. This is used for the multi-edit mode, and as a default for batch edit. You'll see a key icon on that line in the Data Types tab when that's set.

## Populating the database (marathon runners or similar roster)

In typical applications, a CSV file is imported using the Bulk Import section (again under Admin on the top-level menu). You can upload a file here, or you can pick a file that's been copied manually to the .../csvdata/ folder on the server. Either way, it'll guess if it's comma-separated or tab-separted, and move onto to matching input columns with the data types you set up above. You'll be shown the first few lines of the file, and a drop-down menu for each column it extracted from the data file.  Once you've got things matched up, click Import. That sets up an import job -- you should have a Bulk Data Import job visible under the 'Async Jobs' tab on the admin screen. These are processed by the async.php script via the crontab entry set above.

On the Async Jobs page, the 'filename' link returns the input file; clicking the link under Job State provides an error log for that job.

## Design

Initially, the web UI was kept lightweight to be usable over Icom D-STAR 1.2GHz DD mode (~128kbps on a good day). In practice, usage is now almost entirly over WiFi or 4G paths, so some complexity (in the form of the jQuery JavaScript libraries) as crept in, but keeping things usable over slower/lossy connections remains a goal.