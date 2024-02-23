import { Controller, Get, Query } from '@nestjs/common';
import { ApiQuery, ApiTags } from '@nestjs/swagger';
import { DienstenService } from '../service/Diensten.service';
import { ObjectID } from '../../../core/base/ObjectID';
import { DienstenGetObjectsFilterDTO } from '../DTO/DienstenGetObjectsFilterDTO';

@Controller('Diensten')
@ApiTags('Diensten')
export class DienstenController {
  constructor(private readonly dienstenService: DienstenService) {
  }

  @Get('GetObject')
  @ApiQuery({ name: 'ID', type: 'integer', required: true })
  async GetObject(@Query() query: ObjectID) {
    return this.dienstenService.getObject(query.ID);
  }

  @Get('GetObjects')
  @ApiQuery({ name: 'ID', type: 'integer', required: false, description: 'Database ID van het diensten record' })
  @ApiQuery({ name: 'VERWIJDERD', type: 'boolean', required: false, description: 'Toon welke records verwijderd zijn. Default = false' })
  @ApiQuery({ name: 'LAATSTE_AANPASSING', type: 'boolean', required: false, description: 'Laatste aanpassing op basis van records in dataset. Bedoeld om data verbruik te verminderen. Dataset is daarom leeg' })
  @ApiQuery({ name: 'HASH', type: 'string', required: false, description: 'HASH van laatste GetObjects aanroep. Indien bij nieuwe aanroep dezelfde data bevat, dan volgt http status code 304. In geval dataset niet hetzelfde is, dan komt de nieuwe dataset terug. Ook bedoeld om dataverbruik te vermindereren. Er wordt alleen data verzonden als het nodig is.' })
  @ApiQuery({ name: 'SORT', type: 'string', required: false, description: 'Sortering van de velden in ORDER BY formaat. Default = DATUM' })
  @ApiQuery({ name: 'MAX', type: 'integer', required: false, description: 'Maximum aantal records in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'START', type: 'integer', required: false, description: 'Eerste record in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'VELDEN', type: 'string', required: false, description: 'Welke velden moet opgenomen worden in de dataset' })
  @ApiQuery({ name: 'LID_ID', type: 'integer', required: false, description: 'Diensten van een bepaald lid' })
  @ApiQuery({ name: 'DATUM', type: 'string', required: false, description: 'Zoek op datum' })
  @ApiQuery({ name: 'BEGIN_DATUM', type: 'string', required: false, description: 'Begin datum (inclusief deze dag)' })
  @ApiQuery({ name: 'EIND_DATUM', type: 'string', required: false, description: 'Eind datum (inclusief deze dag)' })
  @ApiQuery({ name: 'TYPES', type: 'string', required: false, description: 'Zoek op een of meerder type diensten. Types als CSV formaat' })
  @ApiQuery({ name: 'AANWEZIG', type: 'boolean', required: false, description: 'Haal alle diensten op waar lid aanwezig was' })
  @ApiQuery({ name: 'AFWEZIG', type: 'boolean', required: false, description: 'Haal alle diensten op waar lid NIET aanwezig was' })
  async GetObjects(@Query() query: DienstenGetObjectsFilterDTO) {
    return this.dienstenService.getObjects(query);
  }
}
