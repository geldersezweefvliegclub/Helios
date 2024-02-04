import { Controller, Get, Param, Query } from '@nestjs/common';
import { ApiOperation, ApiQuery, ApiResponse, ApiTags } from '@nestjs/swagger';
import { TypesService } from '../service/types.service';
import { FindManyOptions } from 'typeorm';
import { TypeEntity } from '../entities/Type.entity';

@ApiTags('Types')
@Controller('Types')
export class TypesController {
    constructor(private readonly typesService: TypesService) {}

    @Get('GetObject/:id')
    @ApiOperation({ summary: 'Get object by id' })
    @ApiResponse({ status: 200, description: 'Return the object.' })
    async getObject(@Param('id') id: number) {
        return this.typesService.getObject(id);
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
    @ApiQuery({ name: 'GROEP', required: false, type: Number, description: 'Get all types from a specific group' })
    async getObjects(@Query() filter: FilterCriteria) {
        return this.typesService.getObjects(filter);
    }
}
