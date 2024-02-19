
import { Test, TestingModule } from '@nestjs/testing';
import { RoosterController } from './Rooster.controller';

describe('RoosterController', () => {
  let controller: RoosterController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [RoosterController],
    }).compile();

    controller = module.get<RoosterController>(RoosterController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
