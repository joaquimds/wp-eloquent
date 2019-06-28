# Setting up a new WordPress project

Follow either the "Docker" or "Not Docker" instructions below to get a new WordPress project up and running. 

#### Docker

0. Install the `ol` command line tool ([link](https://gitlab.outlandish.com/outlandish-cli/outlandish-cli))
1. Run `ol proxy start`
2. Fork this repository
3. Run `cp .env.example.docker .env` to enable `ol dev` commands
4. Run `ol dev init` to set up your environment and initialise the project
5. Run `ol dev` to start the project's docker containers
6. The site should now be running at `http://${project_short_name}.local`

#### Not Docker

1. Fork this repository
2. Run `npm i`
3. Run `node init.js` (this will run `composer install`, 
                       import `minimal.sql` into your DB, fill out your `.env`, etc.)
4. Set your webserver to serve the `web` folder
5. Run `npm run watch` to build assets (in dev mode) and watch files

(`npm run build` is used to build assets for production)

### Further Setup

The admin credentials will be `admin:admin`.

### Font Awesome 5 Pro

Copy `.npmrc.example` to `.npmrc` and then replace `FONT_AWESOME_NPM_TOKEN` with your Font Awesome Pro auth token, to allow NPM to install FA Pro

### Advanced Custom Fields 5 Pro

`ACF_PRO_KEY` needs adding to `.env` so Composer can install ACF 5 Pro