
import { Test, TestingModule } from '@nestjs/testing';
import { RoosterController } from './Rooster.controller';
import { RoosterService } from '../service/Rooster.service';

describe('RoosterController', () => {
  let controller: RoosterController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [RoosterController],
      providers: [{provide: RoosterService, useValue: jest.fn()}]
    }).compile();

    controller = module.get<RoosterController>(RoosterController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
