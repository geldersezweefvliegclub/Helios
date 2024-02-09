import { Body, Controller, Delete, Get, HttpCode, Patch, Post, Put, Query } from '@nestjs/common';
import { LedenService } from '../service/Leden.service';
import { ApiOperation, ApiQuery, ApiResponse } from '@nestjs/swagger';
import { LedenGetObjectsFilterDTO } from '../DTO/LedenGetObjectsFilterDTO';
import { ObjectID } from '../../../core/base/ObjectID';
import { LedenEntity } from '../entities/Leden.entity';

@Controller('Leden')
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
  async getObjects(@Query() filter: LedenGetObjectsFilterDTO) {
    return this.ledenService.getObjects(filter);
  }

  @Put('SaveObject')
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
  async restoreObject(@Query() query: ObjectID<LedenGetObjectsFilterDTO>) {
    return this.ledenService.restoreObject(query.ID);
  }

  @Delete('DeleteObject')
  @ApiOperation({ summary: 'Delete type record' })
  @ApiResponse({ status: 204, description: 'Object Deleted' })
  @ApiQuery({ name: 'ID', required: true, type: Number, description: 'The object ID' })
  @HttpCode(204)
  async deleteObject(@Query() query: ObjectID<LedenGetObjectsFilterDTO>) {
    await this.ledenService.deleteObject(query.ID);
  }
}
