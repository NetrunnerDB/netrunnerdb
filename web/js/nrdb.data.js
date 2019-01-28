/* global _, NRDB, Promise */

(function (data, $) {

    var force_update = false;
    var dbNames = ['cycles', 'packs', 'cards', 'factions', 'types', 'sides', 'mwl'];

    /**
     * Promise interface to forerunnerdb's load method
     * @param db a Forerunner database
     */
    data.fdb_load = function (db) {
        return new Promise(function (resolve, reject) {
            db.load(function (err) {
                if (err)
                    reject(err);
                else
                    resolve(db);
            });
        });
    };

    /**
     * loads the database from local
     * sets up a Promise on all data loading/updating
     * @memberOf data
     */
    data.load = function load() {

        var fdb = new ForerunnerDB();
        data.db = fdb.db('netrunnerdb');
        data.masters = {};

        Promise.resolve(dbNames)
            .then(function (dbNames) {
                return Promise.all(_.map(dbNames, function (dbName) {
                    data.masters[dbName] = data.db.collection('master_' + dbName, {primaryKey: 'code', changeTimestamp: true});
                    return data.fdb_load(data.masters[dbName]);
                }));
            })
            .then(function (collections) {
                return Promise.all(_.map(collections, function (collection) {
                    // doing various on the collection to make sure we can run with it
                    var age_of_database = new Date() - new Date(collection.metaData().lastChange);
                    if (age_of_database > 864000000) {
                        console.log('database is older than 10 days => refresh it');
                        collection.setData([]);
                        return false;
                    }
                    if (collection.count() === 0) {
                        console.log('database is empty => load it');
                        return false;
                    }
                    return true;
                }));
            })
            .then(function (collectionsInOrder) {
                console.log('all db successfully reloaded from storage');
                if (!_.every(collectionsInOrder)) {
                    return false;
                }
                return true;
            }, function (message) {
                console.log('error when reloading db', message);
                return false;
            })
            .then(function (allCollectionsInOrder) {
                /*
                 * data has been fetched from local store
                 */
                force_update = !allCollectionsInOrder;

                /*
                 * triggering event that data is loaded
                 */
                if (!force_update) {
                    data.release();
                }
            })
            .then(function () {
                return Promise.all(_.map(dbNames, function (dbName) {
                    return new Promise(function (resolve, reject) {

                        $.ajax(Routing.generate('api_public_' + dbName))
                            .then(function (response, textStatus, jqXHR) {
                                var lastModifiedData = new Date(jqXHR.getResponseHeader('Last-Modified'));
                                var locale = jqXHR.getResponseHeader('Content-Language');
                                var master = data.masters[dbName];
                                var lastChangeDatabase = new Date(master.metaData().lastChange);
                                var lastLocale = master.metaData().locale;
                                var isCollectionUpdated = false;

                                if (dbName === 'cards') {
                                    response.data.forEach(function (card) {
                                        card.imageUrl = card.image_url || response.imageUrlTemplate.replace(/{code}/, card.code);
                                    });
                                }

                                /*
                                 * if we decided to force the update,
                                 * or if the database is fresh,
                                 * or if the database is older than the data,
                                 * or if the locale has changed
                                 * then we update the database
                                 */
                                if (force_update || !lastChangeDatabase || lastChangeDatabase < lastModifiedData || locale !== lastLocale) {
                                    console.log(dbName + ' data is newer than database or update forced or locale has changed => update the database');
                                    master.setData(response.data);
                                    master.metaData().locale = locale;
                                    isCollectionUpdated = (locale === lastLocale);
                                }

                                master.save(function (err) {
                                    if (err) {
                                        console.log('error when saving ' + dbName, err);
                                        reject(true);
                                    } else {
                                        resolve(isCollectionUpdated);
                                    }
                                });
                            })
                            .catch(function (jqXHR, textStatus, errorThrown) {
                                console.log('error when requesting packs', errorThrown || jqXHR);
                                reject(false);
                            });
                    });
                }));
            })
            .then(function (collectionsUpdated) {
                if (force_update) {
                    data.release();
                    return;
                }

                if (_.find(collectionsUpdated)) {
                    /*
                     * we display a message informing the user that they can reload their page to use the updated data
                     * except if we are on the front page, because data is not essential on the front page
                     */
                    NRDB.ui.showBanner("A new version of the data is available. Click <a href=\"javascript:window.location.reload(true)\">here</a> to reload your page.");
                }

            }, function (dataLoaded) {
                /*
                 * if not all data has been loaded, we can't run the site properly
                 */
                if (!_.every(dataLoaded)) {
                    NRDB.ui.showBanner("Unable to load the data. Click <a href=\"javascript:window.location.reload(true)\">here</a> to reload your page.");
                    return;
                } else {
                    data.release();
                }
            });
    };

    /**
     * release the data for consumption by other modules
     * @memberOf data
     */
    data.release = function release() {
        _.each(dbNames, function (dbName) {
            data[dbName] = data.db.collection(dbName, {primaryKey: 'code', changeTimestamp: false});
            data[dbName].setData(data.masters[dbName].find());
        });

        _.each(data.types.find(), function (type) {
            data.types.updateById(type.code, {
                side: data.sides.findById(type.side_code)
            });
        });

        _.each(data.packs.find(), function (pack) {
            data.packs.updateById(pack.code, {
                cycle: data.cycles.findById(pack.cycle_code)
            });
        });

        _.each(data.factions.find(), function (faction) {
            data.factions.updateById(faction.code, {
                side: data.sides.findById(faction.side_code)
            });
        });

        _.each(data.cards.find(), function (card) {
            data.cards.updateById(card.code, {
                faction: data.masters.factions.findById(card.faction_code),
                side: data.sides.findById(card.side_code),
                type: data.types.findById(card.type_code),
                pack: data.packs.findById(card.pack_code)
            });
        });

        data.isLoaded = true;

        $(document).trigger('data.app');
    };

    /**
     * triggers a forced update of the database
     * @memberOf data
     */
    data.update = function update() {
        _.each(data.masters, function (collection) {
            collection.drop();
        });
        data.load();
    };

    data.promise = new Promise(function (resolve, reject) {
        $(document).on('data.app', resolve);
    });

    $(function () {
        data.load();
    });

})(NRDB.data = {}, jQuery);
