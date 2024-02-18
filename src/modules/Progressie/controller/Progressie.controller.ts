import {Controller, Get, NotImplementedException, Query} from '@nestjs/common';
import {ApiOperation, ApiQuery, ApiResponse, ApiTags} from '@nestjs/swagger';
import {ProgressieService} from '../service/Progressie.service';
import {ProgressieGetObjectsFilterDTO} from '../DTO/ProgressieGetObjectsFilterDTO';
import {ProgressieKaartFilterDTO} from "../DTO/ProgressieKaartFilterDTO";
import {GetObjectsResponse} from "../../../core/base/GetObjectsResponse";
import {ProgressiekaartDTO} from "../DTO/ProgressiekaartDTO";

@Controller('Progressie')
@ApiTags('Progressie')
export class ProgressieController {
  constructor(private readonly progressieService: ProgressieService) {
  }

  @Get('GetObject')
  @ApiOperation({ summary: 'Get object by id' })
  @ApiResponse({ status: 200, description: 'Return the object.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'The object ID' })
  async getObject(@Query() query: { ID: number }) {
    return this.progressieService.getObject(query.ID);
  }

  @Get('GetObjects')
  @ApiOperation({ summary: 'Get objects' })
  @ApiResponse({ status: 200, description: 'Return the objects.' })
  @ApiQuery({ name: 'ID', required: false, type: Number, description: 'Database ID van het progressie record' })
  @ApiQuery({ name: 'VERWIJDERD', required: false, type: Boolean, description: 'Toon welke records verwijderd zijn. Default = false' })
  @ApiQuery({ name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Laatste aanpassing op basis van records in dataset. Bedoeld om data verbruik te verminderen. Dataset is daarom leeg' })
  @ApiQuery({ name: 'HASH', required: false, type: String, description: 'HASH van laatste GetObjects aanroep. Indien bij nieuwe aanroep dezelfde data bevat, dan volgt http status code 304. In geval dataset niet hetzelfde is, dan komt de nieuwe dataset terug. Ook bedoeld om dataverbruik te vermindereren. Er wordt alleen data verzonden als het nodig is.' })
  @ApiQuery({ name: 'SORT', required: false, type: String, description: 'Sortering van de velden in ORDER BY formaat. Default = CLUBKIST DESC, VOLGORDE, REGISTRATIE' })
  @ApiQuery({ name: 'MAX', required: false, type: Number, description: 'Maximum aantal records in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'START', required: false, type: Number, description: 'Eerste record in de dataset. Gebruikt in LIMIT query' })
  @ApiQuery({ name: 'VELDEN', required: false, type: String, description: 'Welke velden moet opgenomen worden in de dataset' })
  @ApiQuery({ name: 'INSTRUCTEUR_ID', required: false, type: String, description: 'Welke instruct heeft welke comptententie afgetekend' })
  @ApiQuery({ name: 'LID_ID', required: false, type: String, description: 'Progressie van een bepaald lid' })
  @ApiQuery({ name: 'IN', required: false, type: String, description: 'Comptententie ID\'s in CSV formaat' })
  async getObjects(@Query() query: ProgressieGetObjectsFilterDTO) {
    return this.progressieService.getObjects(query);
  }

  @Get('Progressiekaart')
  @ApiOperation({summary: 'Haal alle comptenties en progressie op en zet deze in een datset (dezelfde data als progressieboom)'})
  @ApiResponse({status: 200, description: 'OK, data succesvol opgehaald'})
  @ApiQuery({name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Laatste aanpassing op basis van records in dataset. Bedoeld om data verbruik te verminderen. Dataset is daarom leeg'})
  @ApiQuery({name: 'VELDEN', required: false, type: String, description: 'Welke velden moet opgenomen worden in de dataset'})
  @ApiQuery({name: 'LID_ID', required: false, type: String, description: 'Progressiekaart van een bepaald lid'})
  async getProgressiekaart(@Query() query: ProgressieKaartFilterDTO): Promise<GetObjectsResponse<ProgressiekaartDTO>> {
    return this.progressieService.getProgressiekaart(query) as Promise<GetObjectsResponse<ProgressiekaartDTO>>;
  }

  @Get('Progressieboom')
    @ApiOperation({summary: 'Haal alle comptenties en progressie op en zet deze in een datset (dezelfde data als progressiekaart)'})
    @ApiResponse({status: 200, description: 'OK, data succesvol opgehaald'})
    @ApiQuery({name: 'LAATSTE_AANPASSING', required: false, type: Boolean, description: 'Laatste aanpassing op basis van records in dataset. Bedoeld om data verbruik te verminderen. Dataset is daarom leeg'})
    @ApiQuery({name: 'VELDEN', required: false, type: String, description: 'Welke velden moet opgenomen worden in de dataset'})
    @ApiQuery({name: 'LID_ID', required: false, type: String, description: 'Progressieboom van een bepaald lid'})
  async getProgressieboom(@Query() query: ProgressieKaartFilterDTO){
    // todo
    throw new NotImplementedException()
  }
}
