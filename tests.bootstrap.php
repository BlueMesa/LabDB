<?php

/*
 * Copyright 2013 Radoslaw Kamil Ejsmont <radoslaw@ejsmont.net>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

if (false !== ($env = getenv('BOOTSTRAP_CLEAR_CACHE_ENV'))) {
    passthru(sprintf(
        'php "bin/console" cache:clear --env=%s --no-warmup', $env
    ));
}

if (false !== ($env = getenv('BOOTSTRAP_TEST_ENV'))) {
    passthru(sprintf(
        'php "bin/console" test:bootstrap --env=%s', $env
    ));
}

if (false !== ($env = getenv('BOOTSTRAP_DROP_DB_ENV'))) {
    passthru(sprintf(
        'php "bin/console" doctrine:schema:drop --force --env=%s', $env
    ));
}

if (false !== ($env = getenv('BOOTSTRAP_CREATE_DB_ENV'))) {
    passthru(sprintf(
        'php "bin/console" doctrine:schema:create --env=%s', $env
    ));
}

if (false !== ($env = getenv('BOOTSTRAP_LOAD_FIXTURES_ENV'))) {
    passthru(sprintf(
        'php "bin/console" doctrine:fixtures:load --no-interaction --fixtures=src/VIB/UserBundle/Tests/DataFixtures --fixtures=src/VIB/FliesBundle/Tests/DataFixtures --env=%s', $env
    ));
}

#require 'var/bootstrap.php.cache';
require 'app/autoload.php';
