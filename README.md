# Getting started
Prerequisite: Have Node.js and Docker installed

1. Clone the repository
2. Run `npm install`
3. Run `docker-compose up` to set up Seq (logging server)
4. Run `docker-compose up` inside the `php/dockr_dev` folder to set up the PHP application w/ database. See legacy READMe.md for more information to get the PHP application running
5. Run `npm run start` to start the NestJS application
6. To test the NestJS application against the PHP application, run `npm run test:e2e`
7. For documentation, run `npm run docs:serve` to serve the documenation, or run `npm run docs:gen` to generate the static html / css files containing the documentation

# Todo list to convert PHP to NestJS

- [x] Setup e2e testing for GetObject and GetObjects - Compare response of NestJS and PHP
- [x] Convert GETs ref_types
- [x] Convert GETs ref_types_groepen
- [ ] Convert GETs ref_leden
  - Done but response from NestJS is different and I don't understand how PHP does everything. Especially around `SECRET` and `PASSWORD`
- [x] Convert GETs ref_vliegtuigen
- [x] Convert GETs ref_competenties
- [ ] Convert GETs oper_progressie
  - But PHP API returns inconsistent date format compared to previous endpoints. It includes the time
  - TODO: progressieboom
  - TODO: progressiekaart laatste_aanpassing incorrect
- [x] Convert GETs oper_rooster
- [ ] Convert GETs oper_diensten
- [ ] Convert GETs oper_journaal
- [ ] Convert GETs oper_daginfo
- [ ] Convert GETs oper_dagrapporten
- [ ] Convert GETs oper_transacties
- [ ] Convert GETs oper_reservering
- [ ] Convert GETs oper_aanwezig_vliegtuigen
- [ ] Convert GETs oper_aanwezig_leden
- [ ] Convert GETs oper_startlijst
- [ ] Convert GETs oper_tracks
- [ ] Build Login endpoints including reset password, Google authenticator, etc.
- [ ] Build authentication and authorization
- [ ] Build permanent e2e / unit tests for authentication and authorization
- [ ] Setup e2e testing for CRUD
  - Deploy the PHP application with docker with its own database 
  - Deploy another database instance with the same initial data for the NestJS application
  - Configure NestJS to use the second database instance
  - Setup generic e2e tests for CRUD operations
- [ ] Convert POSTs
- [ ] Convert PUTs
- [ ] Convert DELETEs

# Todo list to convert PHP to NestJS - Extra
- [ ] Setup sonarcloud analysis
- [ ] Setup Continuous Deployment for main branch
- [ ] Fix ugly HTTP 500 error when VELDEN filter includes a VELD that does not exist
- [ ] Generate migrations
- [ ] Setup initial dataseed for empty database 
