FROM php:7.3.3-cli-alpine3.9

ENV FOLDER=/usr/local/lib/php/dallgoot/yaml


COPY install_composer.sh $FOLDER

RUN chmod a+x $FOLDER/install_composer.sh && sh $FOLDER/install_composer.sh

RUN cd $FOLDER && composer install

CMD ['sh']