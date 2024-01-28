import {Body, Controller, Delete, Get, Param, Patch, Post} from '@nestjs/common';
import {ApiOperation, ApiResponse, ApiTags} from "@nestjs/swagger";
import {TypesService} from "../service/types.service";
import {SaveObjectDto} from "../DTO/SaveObjectDto";
import {CreateTableDto} from "../DTO/CreateTableDto";

@ApiTags('Types')
@Controller('Types')
@Controller('Types')
export class TypesController {
    constructor(private readonly typesService: TypesService) {}

    @Post('CreateTable')
    @ApiOperation({ summary: 'Create table' })
    @ApiResponse({ status: 201, description: 'Table created.' })
    async createTable(@Body() createTypeDto: CreateTableDto) {
        return this.typesService.createTable(createTypeDto);
    }

    @Post('CreateViews')
    @ApiOperation({ summary: 'Create views' })
    @ApiResponse({ status: 201, description: 'Views created.' })
    async createViews() {
        return this.typesService.createViews();
    }

    @Get('GetObject/:id')
    @ApiOperation({ summary: 'Get object by id' })
    @ApiResponse({ status: 200, description: 'Return the object.' })
    async getObject(@Param('id') id: number) {
        return this.typesService.getObject(id);
    }

    // Implement other methods similarly...

    @Delete('DeleteObject/:id')
    @ApiOperation({ summary: 'Delete object by id' })
    @ApiResponse({ status: 204, description: 'Object deleted.' })
    async deleteObject(@Param('id') id: number) {
        return this.typesService.deleteObject(id);
    }

    @Patch('RestoreObject/:id')
    @ApiOperation({ summary: 'Restore object by id' })
    @ApiResponse({ status: 202, description: 'Object restored.' })
    async restoreObject(@Param('id') id: number) {
        return this.typesService.restoreObject(id);
    }

    @Post('SaveObject')
    @ApiOperation({ summary: 'Save object' })
    @ApiResponse({ status: 201, description: 'Object saved.' })
    async saveObject(@Body() body: SaveObjectDto) {
        return this.typesService.saveObject(body);
    }
}
