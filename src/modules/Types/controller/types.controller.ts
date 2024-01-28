import {Body, Controller, Delete, Get, Param, Patch, Post} from '@nestjs/common';
import {ApiOperation, ApiResponse, ApiTags} from "@nestjs/swagger";
import {TypesService} from "../service/types.service";
import {SaveObjectDto} from "../DTO/SaveObjectDto";
import {CreateTableDto} from "../DTO/CreateTableDto";

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
}
