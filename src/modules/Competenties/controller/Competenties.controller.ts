
import { Controller, Get, Query } from '@nestjs/common';
import { ApiOperation, ApiQuery, ApiResponse, ApiTags } from '@nestjs/swagger';
import { CompetentiesService } from '../service/Competenties.service';
import { CompetentiesGetObjectsFilterDTO } from '../DTO/CompetentiesGetObjectsFilterDTO';

@Controller('Competenties')
@ApiTags('Competenties')
export class CompetentiesController {
  constructor(private readonly competentiesService: CompetentiesService) {}

  @Get('GetObject')
  @ApiOperation({ summary: 'Get object by id' })
  @ApiResponse({ status: 200, description: 'Return the object.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'The object ID' })
  async getObject(@Query() query: { ID: number }) {
    return this.competentiesService.getObject(query.ID);
  }

  @Get('GetObjects')
  @ApiOperation({ summary: 'Get a list of objects based on filters' })
  @ApiResponse({ status: 200, description: 'Return the list of objects.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'Database ID of the existing record' })
  @ApiQuery({ name: 'VERWIJDERD', required: false, type: Boolean, description: 'Show which records are deleted. Default = false' })
  @ApiQuery({ name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Last modification based on records in the dataset. Intended to reduce data consumption. The dataset is therefore empty' })
  @ApiQuery({ name: 'HASH', required: false, type: String, description: 'HASH of the last GetObjects call. If the new call contains the same data, then http status code 304 follows. If the dataset is not the same, then the new dataset comes back. Also intended to reduce data consumption. Data is only sent if necessary.' })
  @ApiQuery({ name: 'SORT', required: false, type: String, description: 'Sorting of the fields in ORDER BY format. Default = CLUBKIST DESC, VOLGORDE, REGISTRATIE' })
  @ApiQuery({ name: 'MAX', required: false, type: Number, description: 'Maximum number of records in the dataset. Used in LIMIT query' })
  @ApiQuery({ name: 'START', required: false, type: Number, description: 'First record in the dataset. Used in LIMIT query' })
  @ApiQuery({ name: 'VELDEN', required: false, type: String, description: 'Which fields should be included in the dataset' })
  @ApiQuery({ name: 'LEERFASE_ID', required: false, type: String, description: 'Get all types from a specific learning phase' })
  async getObjects(@Query() filter: CompetentiesGetObjectsFilterDTO) {
    return this.competentiesService.getObjects(filter);
  }
}
