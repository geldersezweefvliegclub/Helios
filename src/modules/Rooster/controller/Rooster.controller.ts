import { Controller, Get, Query } from '@nestjs/common';
import { ApiOperation, ApiQuery, ApiResponse, ApiTags } from '@nestjs/swagger';
import { RoosterService } from '../service/Rooster.service';
import { ObjectID } from '../../../core/base/ObjectID';
import { RoosterGetObjectsFilterDTO } from '../DTO/RoosterGetObjectsFilterDTO';

@Controller('Rooster')
@ApiTags('Rooster')
export class RoosterController {
  constructor(private readonly roosterService: RoosterService) {
  }

  @Get('GetObject')
  @ApiOperation({ summary: 'Get object by id' })
  @ApiResponse({ status: 200, description: 'Return the object.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'The object ID' })
  async getObject(@Query() query: ObjectID) {
    return this.roosterService.getObject(query.ID);
  }

  @Get('GetObjects')
  @ApiOperation({ summary: 'Get all objects' })
  @ApiResponse({ status: 200, description: 'Return all the objects.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'Database ID van het aanwezig record' })
  @ApiQuery({ name: 'VERWIJDERD', required: false, type: Boolean, description: 'Toon welke records verwijderd zijn. Default = false' })
  @ApiQuery({ name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Laatste aanpassing op basis van records in dataset. Bedoeld om data verbruik te verminderen. Dataset is daarom leeg' })
  @ApiQuery({ name: 'SORT', required: false, type: String, description: 'Sortering van de velden in ORDER BY formaat. Default = DATUM DESC' })
  @ApiQuery({ name: 'MAX', required: false, type: Number, description: 'Maximum aantal records in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'START', required: false, type: Number, description: 'Eerste record in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'VELDEN', required: false, type: String, description: 'Welke velden moet opgenomen worden in de dataset' })
  @ApiQuery({ name: 'DATUM', required: false, type: String, description: 'Zoek op datum' })
  @ApiQuery({ name: 'BEGIN_DATUM', required: false, type: String, description: 'Begin datum (inclusief deze dag)' })
  @ApiQuery({ name: 'EIND_DATUM', required: false, type: String, description: 'Eind datum (inclusief deze dag)' })
  async getObjects(@Query() filter: RoosterGetObjectsFilterDTO) {
    return this.roosterService.getObjects(filter);
  }
}
