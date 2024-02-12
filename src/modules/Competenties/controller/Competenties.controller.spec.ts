
import { Test, TestingModule } from '@nestjs/testing';
import { CompetentiesController } from './Competenties.controller';
import { CompetentiesService } from '../service/Competenties.service';

describe('CompetentiesController', () => {
  let controller: CompetentiesController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [CompetentiesController],
      providers: [{
        provide: CompetentiesService,
        useValue: jest.fn()
      }]
    }).compile();

    controller = module.get<CompetentiesController>(CompetentiesController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
