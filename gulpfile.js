const gulp = require('gulp')
const gulpPhpUnit = require('gulp-phpunit')
const gulpZip = require('gulp-zip')
const gulpDel = require('del')
const minimist = require('minimist')
const fs = require('fs')
const pump = require('pump')
const usage = require('gulp-help-doc')
const { exec } = require('child_process')

const ENV_PRODUCTION = 'production'
const ENV_DEVELOPMENT = 'development'
const PACKAGE_NAME = 'multisite-global-media'
const BASE_PATH = './'
const TMP_DESTINATION_PATH = './dist'
const PACKAGE_PATH = `${TMP_DESTINATION_PATH}/${PACKAGE_NAME}`

const options = minimist(
  process.argv.slice(2),
  {
    string: [
      'distVersion',
      'destination',
    ],
    default: {
      destination: process.destination || BASE_PATH,
    },
  }
)

function setupCheckPackageVersion ({ distVersion }) {
  return function checkPackageVersion (done) {
    return new Promise((resolve, reject) => {
      if (distVersion) {
        done()
      }

      reject('Missing --version option with a semver value.')
    })
  }
}

function setupComposer ({ environment, basePath }) {
  let parameters = ''

  if (environment === ENV_PRODUCTION) {
    parameters = `--prefer-dist --optimize-autoloader --classmap-authoritative --no-dev --working-dir=${basePath}`
  }

  return function composer (done) {
    return exec(
      `composer install ${parameters}`,
      (error, stdout, stderr) => {
        if (error) {
          throw new Error(error)
        }

        done()
      },
    )
  }
}

function setupPhpunit () {
  return function phpunit (done) {
    return exec(
      './vendor/bin/phpunit --testdox',
      (error, stdout, stderr) => {
        if (error) {
          throw new Error(error)
        }

        done()
      }
    )
  }
}

function setupPhpcs () {
  return function phpcs (done) {
    return exec(
      './vendor/bin/phpcs',
      (error, stdout, stderr) => {
        if (error) {
          throw new Error(error)
        }

        done()
      }
    )
  }
}

function setupPsalm() {
  return function psalm(done) {
    return exec(
      './vendor/bin/psalm',
      (error, stdout, stderr) => {
        if (error) {
          throw new Error(error)
        }

        done()
      }
    )
  }
}

function setupCopyFiles ({ sources, destination, basePath }) {
  return function copyPackageFiles (done) {
    return new Promise(() => {
      pump(
        gulp.src(
          sources,
          {
            base: basePath,
          }
        ),
        gulp.dest(destination),
        done,
      )
    })
  }
}

function deleteTemporaryFiles () {
  if (!fs.existsSync(TMP_DESTINATION_PATH)) {
    throw new Error(`Cannot create package, ${TMP_DESTINATION_PATH} doesn't exists.`)
  }

  gulpDel.sync(
    [
      `${TMP_DESTINATION_PATH}/**/.gitignore`,
      `${TMP_DESTINATION_PATH}/**/.gitattributes`,
      `${TMP_DESTINATION_PATH}/**/.travis.yml`,
      `${TMP_DESTINATION_PATH}/**/.scrutinizer.yml`,
      `${TMP_DESTINATION_PATH}/**/.gitattributes`,
      `${TMP_DESTINATION_PATH}/**/.git`,
      `${TMP_DESTINATION_PATH}/**/changelog.txt`,
      `${TMP_DESTINATION_PATH}/**/changelog.md`,
      `${TMP_DESTINATION_PATH}/**/CHANGELOG.md`,
      `${TMP_DESTINATION_PATH}/**/CHANGELOG`,
      `${TMP_DESTINATION_PATH}/**/README`,
      `${TMP_DESTINATION_PATH}/**/README.md`,
      `!${PACKAGE_PATH}/README.md`,
      `${TMP_DESTINATION_PATH}/**/readme.md`,
      `!${PACKAGE_PATH}/readme.md`,
      `${TMP_DESTINATION_PATH}/**/readme.txt`,
      `${TMP_DESTINATION_PATH}/**/CONTRIBUTING.md`,
      `${TMP_DESTINATION_PATH}/**/CONTRIBUTING`,
      `${TMP_DESTINATION_PATH}/**/composer.json`,
      `${TMP_DESTINATION_PATH}/**/composer.lock`,
      `${TMP_DESTINATION_PATH}/**/phpcs.xml`,
      `${TMP_DESTINATION_PATH}/**/phpcs.xml.dist`,
      `${TMP_DESTINATION_PATH}/**/phpunit.xml`,
      `${TMP_DESTINATION_PATH}/**/phpunit.xml.dist`,
      `${TMP_DESTINATION_PATH}/**/bitbucket-pipelines.yml`,
      `${TMP_DESTINATION_PATH}/**/test`,
      `${TMP_DESTINATION_PATH}/**/tests`,
      `${TMP_DESTINATION_PATH}/**/bin`,
      `${TMP_DESTINATION_PATH}/**/Dockerfile`,
      `${TMP_DESTINATION_PATH}/**/Makefile`,
    ],
  )
}

function setupDestinationPackage ({ distVersion, destination, basePath }) {
  return function compressPackage (done) {
    const timeStamp = new Date().getTime()

    deleteTemporaryFiles()

    return new Promise(() => {
      exec(
        `git log -n 1 | head -n 1 | sed -e 's/^commit //' | head -c 8`,
        {},
        (error, stdout) => {
          const shortHash = error ? timeStamp : stdout

          pump(
            gulp.src(`${TMP_DESTINATION_PATH}/**/*`, {
              base: TMP_DESTINATION_PATH,
            }),
            gulpZip(`${PACKAGE_NAME}-${distVersion}-${shortHash}.zip`),
            gulp.dest(
              destination,
              {
                base: TMP_DESTINATION_PATH,
                cwd: basePath,
              },
            ),
            done,
          )
        },
      )
    })
  }
}

function setupCleanDist () {
  return async function cleanDist () {
    await gulpDel(TMP_DESTINATION_PATH)
  }
}

function help () {
  return usage(gulp)
}

const cleanDist = setupCleanDist()

const testsTask = gulp.series(
  setupPhpunit(),
  setupPhpcs(),
  setupPsalm()
)

/**
 * Create the plugin package distribution.
 *
 * @task {dist}
 * @arg {distVersion} Package version, the version must to be conformed to semver.
 * @arg {destination} Where the resulting package zip have to be stored.
 */
exports.dist = gulp.series(
  setupCheckPackageVersion({
    ...options,
    environment: ENV_PRODUCTION
  }),
  cleanDist,
  setupCopyFiles({
    ...options,
    environment: ENV_PRODUCTION,
    basePath: BASE_PATH,
    sources: [
      './assets/**/*',
      './src/**/*',
      './multisite-global-media.php',
      './LICENSE',
      './composer.json',
      './composer.lock',
      './readme.md'
    ],
    destination: PACKAGE_PATH,
  }),
  setupComposer({
    ...options,
    basePath: PACKAGE_PATH,
    environment: ENV_PRODUCTION
  }),
  setupDestinationPackage({
    ...options,
    environment: ENV_PRODUCTION
  }),
  cleanDist
)

/**
 * Setup the Development Environment, usually for the first time
 *
 * @task {setup}
 */
exports.setup = gulp.series(
  setupComposer({ environment: ENV_DEVELOPMENT }),
)

exports.help = help
exports.default = help

/**
 * Run Tests
 *
 * @task {tests}
 */
exports.tests = testsTask
