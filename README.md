
# Script for remapping TemplaVoila structure to gridelements and backend layouts

This repo contains a script that can remap a an existing TemplaVoila structure to gridlements and backend layouts.
Note that this does *not* convert the actual templates them selves.
This step has to be done manually.
There is a command in CMSupdater that will help doing that, the output is a little crude however.

## Determining the column- and layout-names

We operate with the understanding that each layout and column will receive a new name.
There might be overlaps in what columns we are mapping to.
This is often an intended effect, since it will make it easier to switch layout and still have the content in reasonable places.

We have two sections:

- **pages** - for describing mapping on page level templates.
- **fce** - for describing mapping on FCE elements.

The structure for each of them are identical.

### Defining columns

Each TemplaVoila-column is defined but its name as key.
There are two fields:

- **type** - Can be either *column* or *header*.
  - A *column* is just what it says - a column with elements.
  - A *header* is contains inline header element in FCE. This element is converted into a independent header content element and the field is converted into a regurlar column.
- **colPos** - An integer defining the column. When choosing columns consider that if you switch layout, then content should appear in expected places. For page columns avoid using the default 0 - 3 columns. Old and unlinked content could be lurking in these places, and will mess up the rendering. Main column for pages should be 10, second most used columns should be 11 and so on.

### Assigning types

When assigning types the key is the uid of the original *templavoila template object*.
The value is the name or uid of the layout now in use.
In most cases this will be a text string.
You can map several template objects to the same layout.
This is especially useful if you want to consolidate design that have several template objects that are essentially describing the same basic structure.
