import { Controller, Get, Query } from '@nestjs/common';
import { LedenService } from '../service/Leden.service';
import { ApiOperation, ApiQuery, ApiResponse, ApiTags } from '@nestjs/swagger';
import { LedenGetObjectsFilterDTO } from '../DTO/LedenGetObjectsFilterDTO';

@Controller('Leden')
@ApiTags('Leden')
export class LedenController {
  constructor(private readonly ledenService: LedenService) {
  }

  @Get('GetObject')
  @ApiOperation({ summary: 'Get object by id' })
  @ApiResponse({ status: 200, description: 'Return the object.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'The object ID' })
  async getObject(@Query() query: { ID: number }) {
    return this.ledenService.getObject(query.ID);
  }

  @Get('GetObjects')
  @ApiOperation({ summary: 'Get all objects' })
  @ApiResponse({ status: 200, description: 'Return all the objects.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'Database ID van het aanwezig record' })
  @ApiQuery({ name: 'VERWIJDERD', required: false, type: Boolean, description: 'Toon welke records verwijderd zijn. Default = false' })
  @ApiQuery({ name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Laatste aanpassing op basis van records in dataset. Bedoeld om data verbruik te verminderen. Dataset is daarom leeg' })
  @ApiQuery({ name: 'SORT', required: false, type: String, description: 'Sortering van de velden in ORDER BY formaat. Default = NAAM' })
  @ApiQuery({ name: 'MAX', required: false, type: Number, description: 'Maximum aantal records in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'START', required: false, type: Number, description: 'Eerste record in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'VELDEN', required: false, type: String, description: 'Welke velden moet opgenomen worden in de dataset' })
  @ApiQuery({ name: 'SELECTIE', required: false, type: String, description: 'Zoek in de NAAM, TELEFOON, MOBIEL, NOODNUMMER, EMAIL' })
  @ApiQuery({ name: 'IN', required: false, type: String, description: 'Meerdere lid database IDs in CSV formaat' })
  @ApiQuery({ name: 'TYPES', required: false, type: String, description: 'Zoek op een of meerder lid types. Types als CSV formaat' })
  @ApiQuery({ name: 'CLUBLEDEN', required: false, type: Boolean, description: 'Wanneer true, toon alleen de leden' })
  @ApiQuery({ name: 'INSTRUCTEURS', required: false, type: Boolean, description: 'Wanneer true, toon alleen de instructeurs' })
  @ApiQuery({ name: 'DDWV_CREW', required: false, type: Boolean, description: 'Wanneer true, toon alleen de DDWV crew' })
  @ApiQuery({ name: 'LIERISTEN', required: false, type: Boolean, description: 'Wanneer true, toon alleen de lieristen' })
  @ApiQuery({ name: 'LIO', required: false, type: Boolean, description: 'Wanneer true, toon alleen de lieristen in opleiding' })
  @ApiQuery({ name: 'STARTLEIDERS', required: false, type: Boolean, description: 'Wanneer true, toon alleen de startleiders' })
  async getObjects(@Query() filter: LedenGetObjectsFilterDTO) {
    return this.ledenService.getObjects(filter);
  }

 /* @Put('SaveObject')
  @ApiOperation({ summary: 'Update existing type record' })
  @ApiResponse({ status: 200, description: 'Return the updated object.' })
  async updateObject(@Body() body: Partial<LedenEntity>) {
    return this.ledenService.updateObject(body);
  }

  @Post('SaveObject')
  @ApiOperation({ summary: 'Add new type record' })
  @ApiResponse({ status: 200, description: 'Return the added object.' })
  async addObject(@Body() data: LedenEntity) {
    return this.ledenService.addObject(data);
  }

  @Patch('RestoreObject')
  @ApiOperation({ summary: 'Restore deleted type record' })
  @ApiQuery({ name: 'ID', required: true, type: Number, description: 'The object ID' })
  @HttpCode(202)
  async restoreObject(@Query() query: ObjectID) {
    return this.ledenService.restoreObject(query.ID);
  }

  @Delete('DeleteObject')
  @ApiOperation({ summary: 'Delete type record' })
  @ApiResponse({ status: 204, description: 'Object Deleted' })
  @ApiQuery({ name: 'ID', required: true, type: Number, description: 'The object ID' })
  @HttpCode(204)
  async deleteObject(@Query() query: ObjectID) {
    await this.ledenService.deleteObject(query.ID);
  }*/
}
