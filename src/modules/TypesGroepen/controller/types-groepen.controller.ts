import { Controller, Get, Query } from '@nestjs/common';
import { ApiOperation, ApiQuery, ApiResponse, ApiTags } from '@nestjs/swagger';
import { TypesGroepenService } from '../service/types-groepen.service';
import { TypesGroepenGetObjectsFilterDTO } from '../DTO/TypesGroepenGetObjectsFilterDTO';


@ApiTags('TypesGroepen')
@Controller('TypesGroepen')
export class TypesGroepenController {
    constructor(private readonly typesGroepenService: TypesGroepenService) {}

    @Get('GetObject')
    @ApiOperation({ summary: 'Get object by id' })
    @ApiResponse({ status: 200, description: 'Return the object.' })
    @ApiQuery({ name: 'ID', required: false, type: Number, description: 'The object ID' })
    async getObject(@Query() query: { ID: number }) {
        return this.typesGroepenService.getObject(query.ID);
    }

    @Get('GetObjects')
    @ApiOperation({ summary: 'Get all objects' })
    @ApiResponse({ status: 200, description: 'Return all the objects.' })
    @ApiQuery({ name: 'ID', required: false, type: Number, description: 'Database ID of the existing record' })
    @ApiQuery({ name: 'VERWIJDERD', required: false, type: Boolean, description: 'Show which records have been deleted. Default = false' })
    @ApiQuery({ name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Last adjustment based on records in the dataset. Intended to reduce data usage. Dataset is therefore empty' })
    @ApiQuery({ name: 'HASH', required: false, type: String, description: 'HASH of the last GetObjects call. If the same data is included in a new call, the http status code 304 follows. If the dataset is not the same, the new dataset will come back. Also intended to reduce data usage. Data is only sent if necessary.' })
    @ApiQuery({ name: 'SORT', required: false, type: String, description: 'Sorting of the fields in ORDER BY format. Default = CLUBKIST DESC, VOLGORDE, REGISTRATIE' })
    @ApiQuery({ name: 'MAX', required: false, type: Number, description: 'Maximum number of records in the dataset. Used in LIMIT query' })
    @ApiQuery({ name: 'START', required: false, type: Number, description: 'First record in the dataset. Used in LIMIT query' })
    @ApiQuery({ name: 'VELDEN', required: false, type: String, description: 'Which fields should be included in the dataset' })
    async getObjects(@Query() filter: TypesGroepenGetObjectsFilterDTO) {
        return this.typesGroepenService.getObjects(filter);
    }

   /* @Put('SaveObject')
    @ApiOperation({ summary: 'Update existing type record' })
    @ApiResponse({ status: 200, description: 'Return the updated object.' })
    async updateObject(@Body() body: Partial<TypeGroepEntity>) {
        return this.typesGroepenService.updateObject(body);
    }

    @Post('SaveObject')
    @ApiOperation({ summary: 'Add new type record' })
    @ApiResponse({ status: 200, description: 'Return the added object.' })
    async addObject(@Body() typeData: TypeGroepEntity) {
        return this.typesGroepenService.addObject(typeData);
    }

    @Patch('RestoreObject')
    @ApiOperation({ summary: 'Restore deleted type record' })
    @ApiQuery({ name: 'ID', required: true, type: Number, description: 'The object ID' })
    @HttpCode(202)
    async restoreObject(@Query() query: ObjectID) {
        return this.typesGroepenService.restoreObject(query.ID);
    }

    @Delete('DeleteObject')
    @ApiOperation({ summary: 'Delete type record' })
    @ApiResponse({ status: 204, description: 'Object Deleted' })
    @ApiQuery({ name: 'ID', required: true, type: Number, description: 'The object ID' })
    @HttpCode(204)
    async deleteObject(@Query() query: ObjectID) {
        await this.typesGroepenService.deleteObject(query.ID);
    }*/
}
