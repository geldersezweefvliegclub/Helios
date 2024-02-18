import {Test, TestingModule} from '@nestjs/testing';
import {ProgressieController} from './Progressie.controller';
import {ProgressieService} from '../service/Progressie.service';

describe('ProgressieController', () => {
  let controller: ProgressieController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [ProgressieController],
      providers: [{provide: ProgressieService, useValue: {}}],
    }).compile();

    controller = module.get<ProgressieController>(ProgressieController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
