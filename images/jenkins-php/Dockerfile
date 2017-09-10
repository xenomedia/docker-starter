FROM jenkins/jenkins:lts
# if we want to install via apt
USER root
# install php requirements
RUN apt-get update && \
  apt-get install -y php php-cli php-xsl \
  php-json php-curl php-mysqlnd \
  php-xdebug php-intl php-mcrypt php-pear \
  curl git ant php-mbstring
RUN apt-get clean -y
# Create a jenkins "HOME" for composer files.
RUN mkdir -p /home/jenkins/composer
RUN chown -R jenkins:jenkins /home/jenkins
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin/  --filename=composer


# Go back to jenkins user.
USER jenkins

# install plugins
RUN /usr/local/bin/install-plugins.sh checkstyle cloverphp crap4j dry htmlpublisher jdepend plot pmd violations warnings xunit
RUN /usr/local/bin/install-plugins.sh git-client scm-api git bitbucket publish-over-ssh htmlpublisher workflow-aggregator ansicolor lockable-resources pipeline-milestone-step
# Install required php tools.
RUN composer --working-dir="/home/jenkins/composer" -n require phing/phing:2.* notfloran/phing-composer-security-checker:~1.0 \
    phploc/phploc:* phpunit/phpunit:~4.0 pdepend/pdepend:~2.0 phpmd/phpmd:~2.2 sebastian/phpcpd:* \
    squizlabs/php_codesniffer:* mayflower/php-codebrowser:~1.1 codeception/codeception:* \
    drupal/coder:^8.2.11 dealerdirect/phpcodesniffer-composer-installer --prefer-source --no-interaction;
# add drupal coder to phpcs
RUN  /home/jenkins/composer/vendor/bin/phpcs --config-set installed_paths /home/jenkins/composer/vendor/drupal/coder/coder_sniffer

USER root
# link composer to bin for path.
RUN   ln -s /home/jenkins/composer/vendor/bin/pdepend /usr/local/bin/pdepend; \
  ln -s /home/jenkins/composer/vendor/bin/phpcpd /usr/local/bin/phpcpd; \
  ln -s /home/jenkins/composer/vendor/bin/phpcs /usr/local/bin/phpcs; \
  ln -s /home/jenkins/composer/vendor/bin/phploc /usr/local/bin/phploc; \
  ln -s /home/jenkins/composer/vendor/bin/phpmd /usr/local/bin/phpmd; \
  ln -s /home/jenkins/composer/vendor/bin/phpunit /usr/local/bin/phpunit

USER jenkins
