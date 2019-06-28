#!/usr/bin/env node
'use strict'

process.on('unhandledRejection', fail)
process.on('uncaughtException', fail)
process.on('SIGINT', exit)

const fs = require('fs')
const path = require('path')
const log = require('npmlog')
const chalk = require('chalk')
const mysql = require('mysql')
const getPkgJson = require('read-pkg-up')
const envfile = require('envfile')
const getRandomValues = require('get-random-values')
const SqlString = require('sqlstring')
const { execSync } = require('child_process')
const { resolve } = require('path')
const { prompt } = require('inquirer')

const argv = process.argv.join('')
const DRY_RUN = argv.includes('--dry-run')
const DOCKER = argv.includes('--docker')

const STEPS = [
  ['Update package.json', async (project, { dryRun }) => {
    const { pkg } = await getPkgJson()

    Object.assign(project, await prompt([
      {
        type: 'input',
        name: 'short_name',
        message: 'Enter the project short name:',
        validate: noWhitespace
      },
      {
        type: 'input',
        name: 'long_name',
        message: 'Enter the project long name:',
        validate: notEmpty
      },
      {
        type: 'input',
        name: 'description',
        message: 'Enter the project description:',
        validate: notEmpty
      }
    ]))

    pkg.name = project.short_name
    pkg.description = project.description

    // remove init npm script
    delete pkg.scripts.init
    // reset the version to 1.0.0
    pkg.version = '1.0.0'
    // clean up unneeded props (added by `pkg-read-up`)
    ;['readme', '_id'].forEach((key) => delete pkg[key])

    if (dryRun) {
      log.verbose('package.json:')
      console.log(pkg)
    } else {
      const json = JSON.stringify(pkg, null, 2) + '\n'
      fs.writeFileSync('./package.json', json)
    }
  }],

  ['Create .env file', async (project, { dryRun }) => {
    const hostname = `${project.short_name}.local`

    const env = envfile.parseFileSync(DOCKER ? './.env.example.docker' : './.env.example')
    const keys = envfile.parseSync(generateSalts())

    const ENV_VARS = {
      WP_HOME: `http://${hostname}`
    }

    if (DOCKER) {
      Object.assign(ENV_VARS, {
        HOSTNAME_BASE: hostname,
        HOSTNAME_DB: `db.${hostname}`,
        HOSTNAME_MAIL: `mail.${hostname}`
      })
    } else {
      // if we're not using docker, prompt the user to set local-machine-specific variables

      // null indicates mandatory field
      const LOCAL_ENV_VARS = Object.assign({}, ENV_VARS, {
        DB_NAME: project.short_name,
        DB_USER: 'root',
        DB_PASSWORD: '',
        DB_PREFIX: 'wp_',
        ACF_PRO_KEY: null
      })

      Object.assign(ENV_VARS, await prompt(
        Object.keys(LOCAL_ENV_VARS).map((name) => ({
          name,
          type: 'input',
          message: `Enter ${name}`,
          default: LOCAL_ENV_VARS[name],
          validate: (answer) => (
            LOCAL_ENV_VARS[name] === null && !answer.length
              ? 'Required field'
              : true
          )
        })
        )
      ))
    }

    Object.assign(env, keys, ENV_VARS)

    Object.assign(project, { env })

    if (dryRun) {
      log.verbose('.env:')
      console.log(envfile.stringifySync(env))
    } else {
      const envStr = envfile.stringifySync(env)
      fs.writeFileSync('./.env', envStr)
    }
  }],

  ['Update docker nginx settings', async (project, { dryRun }) => {
    const path = resolve(__dirname, './docker/nginx/application.conf')

    const applicationConf = fs
      .readFileSync(path).toString()
      .replace(/server_name (.*);/, `server_name ${project.short_name}.local;`)

    if (dryRun) {
      log.verbose('application.conf:')
      console.log(applicationConf)
    } else {
      fs.writeFileSync(path, applicationConf)
    }

    log.info('done')
  }],

  ['Update docker build settings', async (project, { dryRun }) => {
    const path = resolve(__dirname, './build.yml')

    const buildYml = fs
      .readFileSync(path).toString()
      .replace(/PHP_IDE_CONFIG="(.*)"/, `PHP_IDE_CONFIG="serverName=${project.short_name}.local"`)

    if (dryRun) {
      log.verbose('build.yml:')
      console.log(buildYml)
    } else {
      fs.writeFileSync(path, buildYml)
    }

    log.info('done')
  }],

  ['Import data/minimal.sql', async (project, { dryRun }) => {
    if (dryRun) {
      log.info('skipping (dry-run)')
      return
    }

    const adapter = mysqlAdapter(project)

    // connect without db first in order to create it if it doesn't exist
    await adapter.connect()
    await adapter.query(`CREATE DATABASE IF NOT EXISTS ${project.env.DB_NAME};`)

    // then re-connect with the database and run minimal.sql
    await adapter.connect(project.env.DB_NAME)

    // set sql_mode = '' before importing minimal.sql to squash date format errors
    await adapter.query(`SET sql_mode = '';` + fs.readFileSync('./data/minimal.sql').toString())

    log.info('done')
  }],

  ['Update WordPress settings', async (project, { dryRun }) => {
    if (dryRun) {
      log.info('skipping (dry-run)')
      return
    }

    const FIND = "'http://wp.localhost'"
    const REPLACE = SqlString.escape(project.env.WP_HOME)

    const adapter = mysqlAdapter(project)

    await adapter.connect(project.env.DB_NAME)

    await adapter.query(`\
UPDATE wp_options SET option_value = ${SqlString.escape(project.long_name)} WHERE option_name = 'blogname';
UPDATE wp_options SET option_value = ${SqlString.escape(project.description)} WHERE option_name = 'blogdescription';
UPDATE wp_options SET option_value = replace(option_value, ${FIND}, ${REPLACE}) WHERE option_name = 'home' OR option_name = 'siteurl';
UPDATE wp_posts SET guid = replace(guid, ${FIND}, ${REPLACE});
UPDATE wp_posts SET post_content = replace(post_content, ${FIND}, ${REPLACE});
UPDATE wp_postmeta SET meta_value = replace(meta_value,${FIND}, ${REPLACE});`)

    log.info('done')
  }],

  ['Define project colours', async (project, { dryRun }) => {
    const validate = (a) => !a || !a.length ? 'Required field' : true

    const { primary } = await prompt([
      {
        name: 'primary',
        type: 'input',
        message: 'Enter principal colour:',
        default: '#337ab7',
        validate
      }
    ])

    const path = resolve(__dirname, './web/app/themes/outlandish/assets/scss/app/vars.scss')

    const varsScss = fs
      .readFileSync(path).toString()
      .replace(/\$principal-color: (.*);/, `$principal-color: ${primary};`)

    if (dryRun) {
      log.verbose('vars.scss:')
      console.log(varsScss)
    } else {
      fs.writeFileSync(path, varsScss)
    }

    log.info('done')
  }],

  ['Create .initialised file', async (project, { dryRun }) => {
    !dryRun && fs.writeFileSync('./.initialised', '')
    log.info('done')
  }],

  ['Install dependencies and build assets', async (project, { dryRun }) => {
    if (dryRun || DOCKER) {
      log.info('skipping (dry-run or docker)')
      return
    }
    log.info(chalk.grey('> composer install'))
    execSync('composer install')
    log.info(chalk.grey('> webpack --config webpack.config.js'))
    execSync('npm run build:dev')
  }]
]

init()

async function init () {
  const project = {}
  let i = 0

  log.info('Outlandish WP Starter\n')

  if (fs.existsSync('./.initialised')) {
    log.error('Found .initialised in root')
    log.info('Exiting...')
    process.exit(1)
  }

  if (DRY_RUN) {
    log.info('--dry-run: no real changes will be made')
  }

  for (const [ stepName, stepFn ] of STEPS) {
    log.info(`${++i}. ${stepName}:`)

    try {
      await stepFn(project, {
        dryRun: DRY_RUN
      })
    } catch (err) {
      console.log()
      fail(err, `Step ${i} failed!`)
    }

    console.log()
  }

  if (!DRY_RUN) {
    const { commit } = await prompt([{
      name: 'commit',
      message: 'Commit the changes?',
      type: 'confirm',
      default: false
    }])

    if (commit) {
      log.info('Committing:')
      try {
        execSync(`git add . && git commit -m "initialise ${project.short_name}"`)
        log.info(chalk.grey(`> git add .`))
        log.info(chalk.grey(`> git commit -m "initialise ${project.short_name}"`))
      } catch (err) {
        log.warn('Git commit failed:', err.message)
      }
    }
  }

  log.info('Done!')
  log.info('Exiting...')

  process.exit(0)
}

function mysqlAdapter (project) {
  return {
    connect (database) {
      return new Promise((resolve, reject) => {
        if (global.mysql) {
          global.mysql.destroy()
        }

        global.mysql = mysql.createConnection({
          database,
          host: project.env.DB_HOST || 'localhost',
          user: project.env.DB_USER,
          password: project.env.DB_PASSWORD,
          multipleStatements: true
        })

        global.mysql.connect(promiseCb(resolve, reject))
      })
    },

    query (sql) {
      return new Promise((resolve, reject) => {
        global.mysql.query(sql, promiseCb(resolve, reject))
      })
    }
  }
}

function promiseCb (res, rej) {
  return (err) => {
    if (err) return rej(err)
    res()
  }
}

function noWhitespace (answer) {
  return !answer.length || /\s+/.test(answer) ? 'Required field (no spaces)' : true
}

function notEmpty (answer) {
  return !answer.length ? 'Required field' : true
}

function fail (err, msg) {
  log.error(msg || 'An error occurred:\n')
  console.log(err.stack + '\n')
  exit(1)
}

function exit (code = 0) {
  if (code === 0) {
    console.log()
    log.info('User exited')
  }
  log.info('Rewinding to HEAD:')
  log.info(chalk.grey('> git reset --hard HEAD'))
  if (!DRY_RUN) {
    execSync('git reset --hard HEAD')
    const initialised = path.resolve(__dirname, '.initialised')
    if (fs.existsSync(initialised)) {
      fs.unlinkSync(initialised)
    }
  }
  log.info('Exiting...')
  process.exit(code)
}

// cribbed from https://roots.io/salts.html
// (c) Austin Pray 2016
// MIT License
// https://github.com/EFForg/OpenWireless/blob/0e0bd06277f7178f840c36a9b799c7659870fa57/app/js/diceware.js#L59
function generateSalts () {
  const getRandom = function (min, max) {
    let rval = 0
    const range = max - min

    const bitsNeeded = Math.ceil(Math.log2(range))
    if (bitsNeeded > 53) {
      throw new Error('We cannot generate numbers larger than 53 bits.')
    }
    const bytesNeeded = Math.ceil(bitsNeeded / 8)
    const mask = Math.pow(2, bitsNeeded) - 1
    // 7776 -> (2^13 = 8192) -1 == 8191 or 0x00001111 11111111

    // Create byte array and fill with N random numbers
    const byteArray = new Uint8Array(bytesNeeded)
    getRandomValues(byteArray)

    let p = (bytesNeeded - 1) * 8
    for (let i = 0; i < bytesNeeded; i++) {
      rval += byteArray[i] * Math.pow(2, p)
      p -= 8
    }

    // Use & to apply the mask and reduce the number of recursive lookups
    rval = rval & mask

    if (rval >= range) {
      // Integer out of acceptable range
      return getRandom(min, max)
    }
    // Return an integer that falls within the range
    return min + rval
  }

  const getRandomChar = function () {
    const minChar = 33 // !
    const maxChar = 126 // ~
    const char = String.fromCharCode(getRandom(minChar, maxChar))
    if (["'", '"', '\\'].some(function (badChar) { return char === badChar })) {
      return getRandomChar()
    }

    return char
  }

  const generateSalt = function () {
    return Array.apply(null, Array(64)).map(getRandomChar).join('')
  }

  const generateEnvLine = function (mode, key) {
    const salt = generateSalt()
    switch (mode) {
      case 'yml':
        return key.toLowerCase() + ': "' + salt + '"'
      default:
        return key.toUpperCase() + "='" + salt + "'"
    }
  }

  const generateFile = function (mode, keys) {
    return keys.map(generateEnvLine.bind(null, mode)).join('\n')
  }

  return generateFile('env', [
    'AUTH_KEY',
    'SECURE_AUTH_KEY',
    'LOGGED_IN_KEY',
    'NONCE_KEY',
    'AUTH_SALT',
    'SECURE_AUTH_SALT',
    'LOGGED_IN_SALT',
    'NONCE_SALT'
  ])
}
