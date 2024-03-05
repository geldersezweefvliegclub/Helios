'use strict';

customElements.define('compodoc-menu', class extends HTMLElement {
    constructor() {
        super();
        this.isNormalMode = this.getAttribute('mode') === 'normal';
    }

    connectedCallback() {
        this.render(this.isNormalMode);
    }

    render(isNormalMode) {
        let tp = lithtml.html(`
        <nav>
            <ul class="list">
                <li class="title">
                    <a href="index.html" data-type="index-link">Helios documentation</a>
                </li>

                <li class="divider"></li>
                ${ isNormalMode ? `<div id="book-search-input" role="search"><input type="text" placeholder="Type to search"></div>` : '' }
                <li class="chapter">
                    <a data-type="chapter-link" href="index.html"><span class="icon ion-ios-home"></span>Getting started</a>
                    <ul class="links">
                        <li class="link">
                            <a href="overview.html" data-type="chapter-link">
                                <span class="icon ion-ios-keypad"></span>Overview
                            </a>
                        </li>
                        <li class="link">
                            <a href="index.html" data-type="chapter-link">
                                <span class="icon ion-ios-paper"></span>README
                            </a>
                        </li>
                                <li class="link">
                                    <a href="dependencies.html" data-type="chapter-link">
                                        <span class="icon ion-ios-list"></span>Dependencies
                                    </a>
                                </li>
                                <li class="link">
                                    <a href="properties.html" data-type="chapter-link">
                                        <span class="icon ion-ios-apps"></span>Properties
                                    </a>
                                </li>
                    </ul>
                </li>
                    <li class="chapter modules">
                        <a data-type="chapter-link" href="modules.html">
                            <div class="menu-toggler linked" data-bs-toggle="collapse" ${ isNormalMode ?
                                'data-bs-target="#modules-links"' : 'data-bs-target="#xs-modules-links"' }>
                                <span class="icon ion-ios-archive"></span>
                                <span class="link-name">Modules</span>
                                <span class="icon ion-ios-arrow-down"></span>
                            </div>
                        </a>
                        <ul class="links collapse " ${ isNormalMode ? 'id="modules-links"' : 'id="xs-modules-links"' }>
                            <li class="link">
                                <a href="modules/AppModule.html" data-type="entity-link" >AppModule</a>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-AppModule-2e03d1d63545496fec17e1585683171df16ac0e6ec1c25b8d8a6796011157a59f544289f75f1c5cea29a1970acfbb5f293b66bf18d325a0feeecc210c6bbc803"' : 'data-bs-target="#xs-injectables-links-module-AppModule-2e03d1d63545496fec17e1585683171df16ac0e6ec1c25b8d8a6796011157a59f544289f75f1c5cea29a1970acfbb5f293b66bf18d325a0feeecc210c6bbc803"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-AppModule-2e03d1d63545496fec17e1585683171df16ac0e6ec1c25b8d8a6796011157a59f544289f75f1c5cea29a1970acfbb5f293b66bf18d325a0feeecc210c6bbc803"' :
                                        'id="xs-injectables-links-module-AppModule-2e03d1d63545496fec17e1585683171df16ac0e6ec1c25b8d8a6796011157a59f544289f75f1c5cea29a1970acfbb5f293b66bf18d325a0feeecc210c6bbc803"' }>
                                        <li class="link">
                                            <a href="injectables/TypeOrmConfigService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >TypeOrmConfigService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/CompetentiesModule.html" data-type="entity-link" >CompetentiesModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' : 'data-bs-target="#xs-controllers-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' :
                                            'id="xs-controllers-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' }>
                                            <li class="link">
                                                <a href="controllers/CompetentiesController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >CompetentiesController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' : 'data-bs-target="#xs-injectables-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' :
                                        'id="xs-injectables-links-module-CompetentiesModule-770eb17fe7f17f9dc68b4c4f07b8cd0c75b70eb84b9bc0bba62621c63ae0306d5d35d22dd4f2e8ced11c81f47c9f3710cf2dd2d5d91ef52c3dba249602be0298"' }>
                                        <li class="link">
                                            <a href="injectables/CompetentiesService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >CompetentiesService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/CoreModule.html" data-type="entity-link" >CoreModule</a>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-CoreModule-5c6b9ad7505bd098fef151e33d0907654edffa9b13db7a4656f5b46f5e61f19b4ca741ac2b5d6d41f99d242992cd4e684adf22ffb849ded3692270e692f3f70f"' : 'data-bs-target="#xs-injectables-links-module-CoreModule-5c6b9ad7505bd098fef151e33d0907654edffa9b13db7a4656f5b46f5e61f19b4ca741ac2b5d6d41f99d242992cd4e684adf22ffb849ded3692270e692f3f70f"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-CoreModule-5c6b9ad7505bd098fef151e33d0907654edffa9b13db7a4656f5b46f5e61f19b4ca741ac2b5d6d41f99d242992cd4e684adf22ffb849ded3692270e692f3f70f"' :
                                        'id="xs-injectables-links-module-CoreModule-5c6b9ad7505bd098fef151e33d0907654edffa9b13db7a4656f5b46f5e61f19b4ca741ac2b5d6d41f99d242992cd4e684adf22ffb849ded3692270e692f3f70f"' }>
                                        <li class="link">
                                            <a href="injectables/FindOptionsBuilder.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >FindOptionsBuilder</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/DienstenModule.html" data-type="entity-link" >DienstenModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' : 'data-bs-target="#xs-controllers-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' :
                                            'id="xs-controllers-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' }>
                                            <li class="link">
                                                <a href="controllers/DienstenController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >DienstenController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' : 'data-bs-target="#xs-injectables-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' :
                                        'id="xs-injectables-links-module-DienstenModule-70deb85d4c7626702fe9f937b895fff2d9236ec6e6268736de26690ea6601803f4ecad6c9ec757a021e129d0b4eb30185d8c619693b324f8cc3c846ebf424728"' }>
                                        <li class="link">
                                            <a href="injectables/DienstenService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >DienstenService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/LedenModule.html" data-type="entity-link" >LedenModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' : 'data-bs-target="#xs-controllers-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' :
                                            'id="xs-controllers-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' }>
                                            <li class="link">
                                                <a href="controllers/LedenController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >LedenController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' : 'data-bs-target="#xs-injectables-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' :
                                        'id="xs-injectables-links-module-LedenModule-cd5ed9cfad3ed2ad5c61619a25dec8bd67c11723fcbc32df9b231a6f6158e8ad603ef21c7b781dbf6640e030959e620c29244a23ce34751231b27f34f361e0d0"' }>
                                        <li class="link">
                                            <a href="injectables/LedenService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >LedenService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/ProgressieModule.html" data-type="entity-link" >ProgressieModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' : 'data-bs-target="#xs-controllers-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' :
                                            'id="xs-controllers-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' }>
                                            <li class="link">
                                                <a href="controllers/ProgressieController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >ProgressieController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' : 'data-bs-target="#xs-injectables-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' :
                                        'id="xs-injectables-links-module-ProgressieModule-eb8232332a0de3c2ba9f0e9f4ee5f61e919c2f1c457a1118c8bd71f9f7879a8c73b4dbee2ef1818b95c5ab613882fefe1aef5766f3fcf3fecc54d267aa74e199"' }>
                                        <li class="link">
                                            <a href="injectables/ProgressieService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >ProgressieService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/RoosterModule.html" data-type="entity-link" >RoosterModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' : 'data-bs-target="#xs-controllers-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' :
                                            'id="xs-controllers-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' }>
                                            <li class="link">
                                                <a href="controllers/RoosterController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >RoosterController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' : 'data-bs-target="#xs-injectables-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' :
                                        'id="xs-injectables-links-module-RoosterModule-dbccee07eaec3913fef0a590cbfabfe4d6e7d41deb150e2bcb2a2c8e72a28556a4bc1d0c316cfbede82b0b7cab81fad15ea3749b6126465c8051161f509059cb"' }>
                                        <li class="link">
                                            <a href="injectables/RoosterService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >RoosterService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/TypesGroepenModule.html" data-type="entity-link" >TypesGroepenModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' : 'data-bs-target="#xs-controllers-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' :
                                            'id="xs-controllers-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' }>
                                            <li class="link">
                                                <a href="controllers/TypesGroepenController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >TypesGroepenController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' : 'data-bs-target="#xs-injectables-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' :
                                        'id="xs-injectables-links-module-TypesGroepenModule-826edbb8d42225670068ec44b993cd8b3e3eb204a1bed3cd23bb867f560c8b2d2922e23390598616f6165dcff37b26a195d83165bf3c04868ce1a3ef88d77bb6"' }>
                                        <li class="link">
                                            <a href="injectables/TypesGroepenService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >TypesGroepenService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/TypesModule.html" data-type="entity-link" >TypesModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' : 'data-bs-target="#xs-controllers-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' :
                                            'id="xs-controllers-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' }>
                                            <li class="link">
                                                <a href="controllers/TypesController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >TypesController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' : 'data-bs-target="#xs-injectables-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' :
                                        'id="xs-injectables-links-module-TypesModule-e54685dc8c2764b4151d2596c3210d9752769bcb61206bc0a598e85af4d4907f09aca425139d2f948a157c6e2b722de996cb7bf295c2f93f5b2f74c8ec66aaf8"' }>
                                        <li class="link">
                                            <a href="injectables/TypesService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >TypesService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                            <li class="link">
                                <a href="modules/VliegtuigenModule.html" data-type="entity-link" >VliegtuigenModule</a>
                                    <li class="chapter inner">
                                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                            'data-bs-target="#controllers-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' : 'data-bs-target="#xs-controllers-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' }>
                                            <span class="icon ion-md-swap"></span>
                                            <span>Controllers</span>
                                            <span class="icon ion-ios-arrow-down"></span>
                                        </div>
                                        <ul class="links collapse" ${ isNormalMode ? 'id="controllers-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' :
                                            'id="xs-controllers-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' }>
                                            <li class="link">
                                                <a href="controllers/VliegtuigenController.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >VliegtuigenController</a>
                                            </li>
                                        </ul>
                                    </li>
                                <li class="chapter inner">
                                    <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ?
                                        'data-bs-target="#injectables-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' : 'data-bs-target="#xs-injectables-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' }>
                                        <span class="icon ion-md-arrow-round-down"></span>
                                        <span>Injectables</span>
                                        <span class="icon ion-ios-arrow-down"></span>
                                    </div>
                                    <ul class="links collapse" ${ isNormalMode ? 'id="injectables-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' :
                                        'id="xs-injectables-links-module-VliegtuigenModule-94a0691e5851b15000c7d5768310341b1d53146abb5ffa229df5b89cb9c8335410521ac7cadb212c50375e60a8c11c03989d4448fad38a05a49ade711941e1b3"' }>
                                        <li class="link">
                                            <a href="injectables/VliegtuigenService.html" data-type="entity-link" data-context="sub-entity" data-context-id="modules" >VliegtuigenService</a>
                                        </li>
                                    </ul>
                                </li>
                            </li>
                </ul>
                </li>
                        <li class="chapter">
                            <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ? 'data-bs-target="#controllers-links"' :
                                'data-bs-target="#xs-controllers-links"' }>
                                <span class="icon ion-md-swap"></span>
                                <span>Controllers</span>
                                <span class="icon ion-ios-arrow-down"></span>
                            </div>
                            <ul class="links collapse " ${ isNormalMode ? 'id="controllers-links"' : 'id="xs-controllers-links"' }>
                                <li class="link">
                                    <a href="controllers/CompetentiesController.html" data-type="entity-link" >CompetentiesController</a>
                                </li>
                                <li class="link">
                                    <a href="controllers/DienstenController.html" data-type="entity-link" >DienstenController</a>
                                </li>
                                <li class="link">
                                    <a href="controllers/LedenController.html" data-type="entity-link" >LedenController</a>
                                </li>
                                <li class="link">
                                    <a href="controllers/ProgressieController.html" data-type="entity-link" >ProgressieController</a>
                                </li>
                                <li class="link">
                                    <a href="controllers/RoosterController.html" data-type="entity-link" >RoosterController</a>
                                </li>
                                <li class="link">
                                    <a href="controllers/TypesController.html" data-type="entity-link" >TypesController</a>
                                </li>
                                <li class="link">
                                    <a href="controllers/TypesGroepenController.html" data-type="entity-link" >TypesGroepenController</a>
                                </li>
                                <li class="link">
                                    <a href="controllers/VliegtuigenController.html" data-type="entity-link" >VliegtuigenController</a>
                                </li>
                            </ul>
                        </li>
                        <li class="chapter">
                            <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ? 'data-bs-target="#entities-links"' :
                                'data-bs-target="#xs-entities-links"' }>
                                <span class="icon ion-ios-apps"></span>
                                <span>Entities</span>
                                <span class="icon ion-ios-arrow-down"></span>
                            </div>
                            <ul class="links collapse " ${ isNormalMode ? 'id="entities-links"' : 'id="xs-entities-links"' }>
                                <li class="link">
                                    <a href="entities/AuditEntity.html" data-type="entity-link" >AuditEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/CompetentiesEntity.html" data-type="entity-link" >CompetentiesEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/DienstenEntity.html" data-type="entity-link" >DienstenEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/LedenEntity.html" data-type="entity-link" >LedenEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/ProgressieEntity.html" data-type="entity-link" >ProgressieEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/RoosterEntity.html" data-type="entity-link" >RoosterEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/TypeEntity.html" data-type="entity-link" >TypeEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/TypeGroepEntity.html" data-type="entity-link" >TypeGroepEntity</a>
                                </li>
                                <li class="link">
                                    <a href="entities/VliegtuigenEntity.html" data-type="entity-link" >VliegtuigenEntity</a>
                                </li>
                            </ul>
                        </li>
                    <li class="chapter">
                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ? 'data-bs-target="#classes-links"' :
                            'data-bs-target="#xs-classes-links"' }>
                            <span class="icon ion-ios-paper"></span>
                            <span>Classes</span>
                            <span class="icon ion-ios-arrow-down"></span>
                        </div>
                        <ul class="links collapse " ${ isNormalMode ? 'id="classes-links"' : 'id="xs-classes-links"' }>
                            <li class="link">
                                <a href="classes/AuditEntity.html" data-type="entity-link" >AuditEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/CompetentiesEntity.html" data-type="entity-link" >CompetentiesEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/CompetentiesGetObjectsFilterDTO.html" data-type="entity-link" >CompetentiesGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/CompetentiesViewEntity.html" data-type="entity-link" >CompetentiesViewEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/DatabaseLogger.html" data-type="entity-link" >DatabaseLogger</a>
                            </li>
                            <li class="link">
                                <a href="classes/DienstenEntity.html" data-type="entity-link" >DienstenEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/DienstenGetObjectsFilterDTO.html" data-type="entity-link" >DienstenGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/DienstenViewEntity.html" data-type="entity-link" >DienstenViewEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/GetObjectsFilterDTO.html" data-type="entity-link" >GetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/HttpExceptionLogger.html" data-type="entity-link" >HttpExceptionLogger</a>
                            </li>
                            <li class="link">
                                <a href="classes/IHeliosDatabaseEntity.html" data-type="entity-link" >IHeliosDatabaseEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/IHeliosFilterDTO.html" data-type="entity-link" >IHeliosFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/IHeliosService.html" data-type="entity-link" >IHeliosService</a>
                            </li>
                            <li class="link">
                                <a href="classes/InvalidArgumentException.html" data-type="entity-link" >InvalidArgumentException</a>
                            </li>
                            <li class="link">
                                <a href="classes/LedenEntity.html" data-type="entity-link" >LedenEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/LedenGetObjectsFilterDTO.html" data-type="entity-link" >LedenGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/LedenViewEntity.html" data-type="entity-link" >LedenViewEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/ObjectID.html" data-type="entity-link" >ObjectID</a>
                            </li>
                            <li class="link">
                                <a href="classes/ProgressieGetObjectsFilterDTO.html" data-type="entity-link" >ProgressieGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/ProgressiekaartDTO.html" data-type="entity-link" >ProgressiekaartDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/ProgressieKaartFilterDTO.html" data-type="entity-link" >ProgressieKaartFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/ProgressieViewEntity.html" data-type="entity-link" >ProgressieViewEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/RoosterGetObjectsFilterDTO.html" data-type="entity-link" >RoosterGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/RoosterViewEntity.html" data-type="entity-link" >RoosterViewEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/TypeEntity.html" data-type="entity-link" >TypeEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/TypeGroepViewEntity.html" data-type="entity-link" >TypeGroepViewEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/TypesGetObjectsFilterDTO.html" data-type="entity-link" >TypesGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/TypesGroepenGetObjectsFilterDTO.html" data-type="entity-link" >TypesGroepenGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/TypeViewEntity.html" data-type="entity-link" >TypeViewEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/VliegtuigenEntity.html" data-type="entity-link" >VliegtuigenEntity</a>
                            </li>
                            <li class="link">
                                <a href="classes/VliegtuigenGetObjectsFilterDTO.html" data-type="entity-link" >VliegtuigenGetObjectsFilterDTO</a>
                            </li>
                            <li class="link">
                                <a href="classes/VliegtuigenViewEntity.html" data-type="entity-link" >VliegtuigenViewEntity</a>
                            </li>
                        </ul>
                    </li>
                        <li class="chapter">
                            <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ? 'data-bs-target="#injectables-links"' :
                                'data-bs-target="#xs-injectables-links"' }>
                                <span class="icon ion-md-arrow-round-down"></span>
                                <span>Injectables</span>
                                <span class="icon ion-ios-arrow-down"></span>
                            </div>
                            <ul class="links collapse " ${ isNormalMode ? 'id="injectables-links"' : 'id="xs-injectables-links"' }>
                                <li class="link">
                                    <a href="injectables/CompetentiesService.html" data-type="entity-link" >CompetentiesService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/DienstenService.html" data-type="entity-link" >DienstenService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/FindOptionsBuilder.html" data-type="entity-link" >FindOptionsBuilder</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/LedenService.html" data-type="entity-link" >LedenService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/ProgressieService.html" data-type="entity-link" >ProgressieService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/RequestLoggingMiddleware.html" data-type="entity-link" >RequestLoggingMiddleware</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/RoosterService.html" data-type="entity-link" >RoosterService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/TransformInterceptor.html" data-type="entity-link" >TransformInterceptor</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/TypeOrmConfigService.html" data-type="entity-link" >TypeOrmConfigService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/TypesGroepenService.html" data-type="entity-link" >TypesGroepenService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/TypesService.html" data-type="entity-link" >TypesService</a>
                                </li>
                                <li class="link">
                                    <a href="injectables/VliegtuigenService.html" data-type="entity-link" >VliegtuigenService</a>
                                </li>
                            </ul>
                        </li>
                    <li class="chapter">
                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ? 'data-bs-target="#interfaces-links"' :
                            'data-bs-target="#xs-interfaces-links"' }>
                            <span class="icon ion-md-information-circle-outline"></span>
                            <span>Interfaces</span>
                            <span class="icon ion-ios-arrow-down"></span>
                        </div>
                        <ul class="links collapse " ${ isNormalMode ? ' id="interfaces-links"' : 'id="xs-interfaces-links"' }>
                            <li class="link">
                                <a href="interfaces/GetObjectsResponse.html" data-type="entity-link" >GetObjectsResponse</a>
                            </li>
                            <li class="link">
                                <a href="interfaces/IHeliosObject.html" data-type="entity-link" >IHeliosObject</a>
                            </li>
                        </ul>
                    </li>
                    <li class="chapter">
                        <div class="simple menu-toggler" data-bs-toggle="collapse" ${ isNormalMode ? 'data-bs-target="#miscellaneous-links"'
                            : 'data-bs-target="#xs-miscellaneous-links"' }>
                            <span class="icon ion-ios-cube"></span>
                            <span>Miscellaneous</span>
                            <span class="icon ion-ios-arrow-down"></span>
                        </div>
                        <ul class="links collapse " ${ isNormalMode ? 'id="miscellaneous-links"' : 'id="xs-miscellaneous-links"' }>
                            <li class="link">
                                <a href="miscellaneous/functions.html" data-type="entity-link">Functions</a>
                            </li>
                            <li class="link">
                                <a href="miscellaneous/variables.html" data-type="entity-link">Variables</a>
                            </li>
                        </ul>
                    </li>
                    <li class="chapter">
                        <a data-type="chapter-link" href="coverage.html"><span class="icon ion-ios-stats"></span>Documentation coverage</a>
                    </li>
                    <li class="divider"></li>
                    <li class="copyright">
                        Documentation generated using <a href="https://compodoc.app/" target="_blank" rel="noopener noreferrer">
                            <img data-src="images/compodoc-vectorise.png" class="img-responsive" data-type="compodoc-logo">
                        </a>
                    </li>
            </ul>
        </nav>
        `);
        this.innerHTML = tp.strings;
    }
});