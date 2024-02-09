
import { Test, TestingModule } from '@nestjs/testing';
import { LedenController } from './Leden.controller';

describe('LedenController', () => {
  let controller: LedenController;

  beforeEach(async () => {
    const module: TestingModule = await Test.createTestingModule({
      controllers: [LedenController],
    }).compile();

    controller = module.get<LedenController>(LedenController);
  });

  it('should be defined', () => {
    expect(controller).toBeDefined();
  });
});
