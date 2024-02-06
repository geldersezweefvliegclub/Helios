import { Body, Controller, Delete, Get, HttpCode, Patch, Post, Put, Query } from '@nestjs/common';
import { ApiOperation, ApiQuery, ApiResponse, ApiTags } from '@nestjs/swagger';
import { VliegtuigenEntity } from '../entities/Vliegtuigen.entity';
import { VliegtuigenService } from '../service/vliegtuigen.service';
import { VliegtuigenGetObjectsFilterDTO } from '../DTO/VliegtuigenGetObjectsFilterDTO';
import { ObjectID } from '../../../core/DTO/ObjectID';

@ApiTags('Vliegtuigen')
@Controller('Vliegtuigen')
export class VliegtuigenController {
    constructor(private readonly vliegtuigenService: VliegtuigenService) {}

    @Get('GetObject')
    @ApiOperation({ summary: 'Get object by id' })
    @ApiResponse({ status: 200, description: 'Return the object.' })
    @ApiQuery({ name: 'ID', required: false, type: Number, description: 'The object ID' })
    async getObject(@Query() query: { ID: number }) {
        return this.vliegtuigenService.getObject(query.ID);
    }

    @Get('GetObjects')
    @ApiOperation({ summary: 'Get a list of objects based on filters' })
    @ApiResponse({ status: 200, description: 'Return the list of objects.' })
    @ApiQuery({ name: 'ID', required: false, type: Number, description: 'The object ID' })
    @ApiQuery({ name: 'VERWIJDERD', required: false, type: Boolean, description: 'Toon welke records verwijderd zijn. Default = false' })
    @ApiQuery({ name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Laatste aanpassing op basis van records in dataset. Bedoeld om data verbruik te verminderen. Dataset is daarom leeg' })
    @ApiQuery({ name: 'HASH', required: false, type: String, description: 'HASH van laatste GetObjects aanroep. Indien bij nieuwe aanroep dezelfde data bevat, dan volgt http status code 304. In geval dataset niet hetzelfde is, dan komt de nieuwe dataset terug. Ook bedoeld om dataverbruik te vermindereren. Er wordt alleen data verzonden als het nodig is.' })
    @ApiQuery({ name: 'SORT', required: false, type: String, description: 'Sortering van de velden in ORDER BY formaat. Default = CLUBKIST DESC, VOLGORDE, REGISTRATIE' })
    @ApiQuery({ name: 'MAX', required: false, type: Number, description: 'Maximum aantal records in de dataset. Gebruikt in LIMIT query' })
    @ApiQuery({ name: 'START', required: false, type: Number, description: 'Eerste record in de dataset. Gebruikt in LIMIT query' })
    @ApiQuery({ name: 'VELDEN', required: false, type: String, description: 'Welke velden moet opgenomen worden in de dataset' })
    @ApiQuery({ name: 'SELECTIE', required: false, type: String, description: 'Zoek in de REGISTRATIE, CALLSIGN, FLARM_CODE' })
    @ApiQuery({ name: 'IN', required: false, type: String, description: 'Een of meerdere vliegtuig database IDs in CSV formaat. AND conditie als er geen andere parameters zijn, anders OR conditie' })
    @ApiQuery({ name: 'TYPES', required: false, type: String, description: 'Zoek op een of meerder type vliegtuig(en). Types als CSV formaat' })
    @ApiQuery({ name: 'ZITPLAATSEN', required: false, type: Number, description: 'Zoek op zitplaatsen 1/2' })
    @ApiQuery({ name: 'CLUBKIST', required: false, type: Boolean, description: 'Haal alle clubvliegtuigen op' })
    @ApiQuery({ name: 'ZELFSTART', required: false, type: Boolean, description: 'Haal alle zelfstarters op.' })
    @ApiQuery({ name: 'SLEEPKIST', required: false, type: Boolean, description: 'Haal alle sleepkisten op.' })
    @ApiQuery({ name: 'TMG', required: false, type: Boolean, description: 'Haal alle TMGs op.' })
    async getObjects(@Query() filter: VliegtuigenGetObjectsFilterDTO) {
        return this.vliegtuigenService.getObjects(filter);
    }

    @Put('SaveObject')
    @ApiOperation({ summary: 'Update existing vliegtuig record' })
    @ApiResponse({ status: 200, description: 'Return the updated object.' })
    async updateObject(@Body() body: Partial<VliegtuigenEntity>) {
        return this.vliegtuigenService.updateObject(body);
    }

    @Post('SaveObject')
    @ApiOperation({ summary: 'Add new vliegtuig record' })
    @ApiResponse({ status: 200, description: 'Return the added object.' })
    async addObject(@Body() vliegtuigData: VliegtuigenEntity) {
        return this.vliegtuigenService.addObject(vliegtuigData);
    }

    @Patch('RestoreObject')
    @ApiOperation({ summary: 'Restore deleted vliegtuig record' })
    @ApiQuery({ name: 'ID', required: true, type: Number, description: 'The object ID' })
    @HttpCode(202)
    async restoreObject(@Query() query: ObjectID<VliegtuigenGetObjectsFilterDTO>) {
        return this.vliegtuigenService.restoreObject(query.ID);
    }

    @Delete('DeleteObject')
    @ApiOperation({ summary: 'Delete vliegtuig record' })
    @ApiResponse({ status: 204, description: 'Object Deleted' })
    @ApiQuery({ name: 'ID', required: true, type: Number, description: 'The object ID' })
    @HttpCode(204)
    async deleteObject(@Query() query: ObjectID<VliegtuigenGetObjectsFilterDTO>) {
        return this.vliegtuigenService.deleteObject(query.ID);
    }
}
