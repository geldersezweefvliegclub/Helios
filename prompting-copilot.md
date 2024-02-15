Prompts to make Copilot Chat generate some Typescript code, based on the provided PHP code.
Useful for the PHP -> Typescript conversion.

# Generating a TypeORM Entity from PHP
- Provide .php file with GetObject, GetObjects and CreateTable methods
- Provide the typescript file in which the entity should be generated
- Provide the IHeliosDatabaseEntity baseclass
- `Create a typeorm entity based on the provided php file`
- Check the generated entity and make sure it's correct
- Check for inheritance from the base entity


# Generating GetObjectsDTO for the entity from PHP
- Provide .php file with GetObject, GetObjects and CreateTable methods
- Provide another .php file with GetObject, GetObjects and CreateTable method, but from an entity that's already converted to typescript
- Provide the typescript file in which the DTO should be generated
- Provide another typescript file with the DTO from the entity that's already converted to typescript
- Provide the GetObjectsDTO baseclass
- Provide the base class of GetObjectsDTO - IHeliosFilterDTO
- Provide FindOptionsBuilder
- `Put the filters for Progressie into the {{new entity}}GetObjectsFilterDTO, similar to how it's done for {{old entity}}. Include the bouwGetObjectsFindOptions in your answer. Account for the baseclass`
