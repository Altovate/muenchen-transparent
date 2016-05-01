<?php
$I = new OparlTester($scenario);
$I->wantTo('validate the external list oparl:organization');
$I->sendGET('/body/0/list/organization');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/organization/fraktion/2",
      "type": "https://oparl.org/schema/1.0/Organization",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Fraktion des Stadtrat",
      "shortName": "Fraktion des Stadtrat",
      "meeting": [],
      "membership": [],
      "classification": "Fraktion"
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/organization/referat/1",
      "type": "https://oparl.org/schema/1.0/Organization",
      "body": "http://localhost:8080/oparl/v1.0/body/0",
      "name": "Referat für städtische Aufgaben",
      "shortName": "Referat für städtische Aufgaben",
      "meeting": [],
      "membership": [],
      "classification": "Referat"
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/0/list/organization",
  "lastPage": "http://localhost:8080/oparl/v1.0/body/0/list/organization",
  "numberOfPages": 1
}
');
$I->sendGET('/body/1/list/organization');
$I->seeOparl('
{
  "items": [
    {
      "id": "http://localhost:8080/oparl/v1.0/organization/gremium/1",
      "type": "https://oparl.org/schema/1.0/Organization",
      "body": "http://localhost:8080/oparl/v1.0/body/1",
      "name": "Ausschuss mit Terminen",
      "shortName": "Ausschuss mit Terminen",
      "meeting": [],
      "membership": [],
      "classification": "BA-Gremium"
    },
    {
      "id": "http://localhost:8080/oparl/v1.0/organization/fraktion/1",
      "type": "https://oparl.org/schema/1.0/Organization",
      "body": "http://localhost:8080/oparl/v1.0/body/1",
      "name": "Fraktion der Politiker",
      "shortName": "Fraktion der Politiker",
      "meeting": [],
      "membership": [],
      "classification": "Fraktion"
    }
  ],
  "itemsPerPage": 3,
  "firstPage": "http://localhost:8080/oparl/v1.0/body/1/list/organization",
  "lastPage": "http://localhost:8080/oparl/v1.0/body/1/list/organization",
  "numberOfPages": 1
}
');
